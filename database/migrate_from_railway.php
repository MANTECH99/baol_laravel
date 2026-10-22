<?php
/**
 * Migrate data from the Django (Railway) MySQL database into a Laravel MySQL database.
 *
 * Two modes:
 *
 * A) Direct import (target reachable from this machine):
 *   php migrate_from_railway.php --source="mysql://user:pass@host:port/db" \
 *                                 --target="mysql://user:pass@host:port/db" \
 *                                 [--truncate] [--passwords="email1:pass1,email2:pass2"]
 *
 * B) SQL dump (for phpMyAdmin / a remote host you cannot reach, e.g. 127.0.0.1 only):
 *   php migrate_from_railway.php --source="mysql://user:pass@host:port/db" \
 *                                 --out=migrate.sql [--passwords="email1:pass1,email2:pass2"]
 *   Then import migrate.sql in phpMyAdmin on the target database.
 *
 * - Preserves all original ids (run against an EMPTY target, or use --truncate / the
 *   dump which starts with DELETE statements).
 * - Django password hashes (PBKDF2) are NOT portable: users get bcrypt hashes.
 *   Provide plaintext passwords with --passwords, otherwise a temporary one is
 *   generated (see output) and must be changed after login.
 */

if (PHP_SAPI !== 'cli') { exit("CLI only\n"); }

$opts = getopt('', ['source:', 'target:', 'out:', 'truncate', 'passwords:']);
$srcDsn = $opts['source'] ?? null;
$tgtDsn = $opts['target'] ?? null;
$outFile = $opts['out'] ?? null;
$truncate = isset($opts['truncate']);
$pwList = [];
if (!empty($opts['passwords'])) {
    foreach (explode(',', $opts['passwords']) as $pair) {
        [$e, $p] = array_pad(explode(':', $pair, 2), 2, '');
        if ($e !== '' && $p !== '') $pwList[trim($e)] = $p;
    }
}
if (!$srcDsn || (!$tgtDsn && !$outFile)) {
    echo "Usage: php migrate_from_railway.php --source=DSN (--target=DSN [--truncate] | --out=file.sql) [--passwords=...]\n";
    exit(1);
}
if ($tgtDsn && $outFile) { echo "Choose either --target or --out, not both.\n"; exit(1); }

function pdo(string $url): PDO {
    $u = parse_url($url);
    $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        $u['host'] ?? 'localhost', $u['port'] ?? 3306, ltrim($u['path'] ?? '', '/'));
    return new PDO($dsn, rawurldecode($u['user'] ?? ''), rawurldecode($u['pass'] ?? ''), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET sql_mode='STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION'",
    ]);
}
function rows(PDO $pdo, string $sql, array $p = []): array {
    $st = $pdo->prepare($sql); $st->execute($p); return $st->fetchAll();
}
function one(PDO $pdo, string $sql, array $p = []) {
    $st = $pdo->prepare($sql); $st->execute($p); return $st->fetchColumn();
}
function sqlVal(PDO $q, $v): string {
    return $v === null ? 'NULL' : $q->quote((string) $v);
}

$src = pdo($srcDsn);
$tgt = $outFile ? null : pdo($tgtDsn);

// target table => [source table, columns identical on both sides]
$map = [
    'livraisons'     => ['gestion_livraison',    ['id', 'date', 'camion_id', 'tonnage', 'prix_unitaire', 'quantite', 'montant', 'chiffonage', 'numero_bl', 'statut', 'verification']],
    'statut_camions' => ['gestion_statutcamion', ['id', 'camion_id', 'date', 'statut']],
    'camions'        => ['gestion_camion',       ['id', 'numero', 'telephone']],
    'rapports'       => ['gestion_rapport',      ['id', 'mois', 'fichier', 'date_ajout']],
];

echo "== Source (Django) ==\n";
$srcCounts = [];
foreach ($map as $t => [$s, $cols]) {
    $srcCounts[$t] = (int) one($src, "SELECT COUNT(*) FROM `$s`");
    echo sprintf("  %-22s -> %-15s %d\n", $s, $t, $srcCounts[$t]);
}
$srcUsers = (int) one($src, 'SELECT COUNT(*) FROM auth_user');
echo sprintf("  %-22s -> %-15s %d\n", 'auth_user', 'users', $srcUsers);

// ================= MODE B: SQL DUMP =================
if ($outFile) {
    $tmpPassword = bin2hex(random_bytes(6));
    $tmpAccounts = [];
    $fh = fopen($outFile, 'w');
    if (!$fh) { echo "Cannot write $outFile\n"; exit(1); }

    fwrite($fh, "-- Baol: Django (Railway) -> Laravel (Hostinger) data migration\n");
    fwrite($fh, "-- Generated " . date('Y-m-d H:i:s') . "\n");
    fwrite($fh, "-- Import this file into the Laravel database (phpMyAdmin).\n");
    fwrite($fh, "-- Requires the Laravel tables to exist (run `php artisan migrate` first).\n\n");
    fwrite($fh, "SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n");

    foreach ($map as $t => [$s, $cols]) {
        $all = rows($src, "SELECT " . implode(',', array_map(fn($c) => "`$c`", $cols)) . " FROM `$s` ORDER BY id");
        fwrite($fh, "DELETE FROM `$t`;\n");
        if ($all) {
            $colList = implode(', ', array_map(fn($c) => "`$c`", $cols));
            foreach (array_chunk($all, 500) as $chunk) {
                $lines = [];
                foreach ($chunk as $r) {
                    $vals = [];
                    foreach ($cols as $c) $vals[] = sqlVal($src, $r[$c]);
                    $lines[] = '(' . implode(', ', $vals) . ')';
                }
                fwrite($fh, "INSERT INTO `$t` ($colList) VALUES\n" . implode(",\n", $lines) . ";\n");
            }
        }
        fwrite($fh, "\n");
        printf("  dumped %-15s %d rows\n", $t, count($all));
    }

    // users: PBKDF2 hashes are not portable -> bcrypt
    $colU = '`id`, `username`, `password`, `is_admin`, `created_at`, `updated_at`';
    fwrite($fh, "DELETE FROM `users`;\n");
    $n = 0;
    $batch = [];
    foreach (rows($src, 'SELECT id, username, is_superuser, last_login FROM auth_user ORDER BY id') as $u) {
        $plain = $pwList[$u['username']] ?? null;
        if ($plain === null) { $plain = $tmpPassword; $tmpAccounts[] = $u['username']; }
        $now = $u['last_login'] ?? date('Y-m-d H:i:s');
        $vals = [sqlVal($src, $u['id']), sqlVal($src, $u['username']), sqlVal($src, password_hash($plain, PASSWORD_BCRYPT)),
                 sqlVal($src, $u['is_superuser'] ? 1 : 0), sqlVal($src, $now), sqlVal($src, $now)];
        $batch[] = '(' . implode(', ', $vals) . ')';
        $n++;
    }
    if ($batch) fwrite($fh, "INSERT INTO `users` ($colU) VALUES\n" . implode(",\n", $batch) . ";\n");
    fwrite($fh, "\nSET FOREIGN_KEY_CHECKS=1;\n");
    fclose($fh);
    printf("  dumped %-15s %d rows\n", 'users', $n);

    if ($tmpAccounts) {
        echo "\n  WARNING: no plaintext password given for: " . implode(', ', $tmpAccounts) . "\n";
        echo "  They were all set to temporary password: $tmpPassword\n";
        echo "  Change them immediately (or re-run with --passwords=\"email:pass,...\").\n";
    }
    echo "\nDump written: $outFile (" . number_format(filesize($outFile)) . " bytes)\n";
    echo "  tables emptied+refilled by the dump: " . implode(', ', array_keys($map)) . ", users\n";
    exit(0);
}

// ================= MODE A: DIRECT IMPORT =================
$nonEmpty = [];
foreach ($map as $t => [$s, $cols]) {
    $c = (int) one($tgt, "SELECT COUNT(*) FROM `$t`");
    if ($c > 0) $nonEmpty[] = "$t=$c";
}
$uCount = (int) one($tgt, 'SELECT COUNT(*) FROM users');
if ($uCount > 0) $nonEmpty[] = "users=$uCount";

if ($nonEmpty && !$truncate) {
    echo "\nTarget NOT empty (" . implode(', ', $nonEmpty) . "). Re-run with --truncate to wipe it first.\n";
    exit(1);
}
if ($nonEmpty && $truncate) {
    echo "\n--truncate: wiping target tables...\n";
    $tgt->exec('SET FOREIGN_KEY_CHECKS=0');
    foreach (array_keys($map) as $t) $tgt->exec("DELETE FROM `$t`");
    $tgt->exec('DELETE FROM users');
    $tgt->exec('SET FOREIGN_KEY_CHECKS=1');
}

$tgt->beginTransaction();
try {
    foreach ($map as $t => [$s, $cols]) {
        $n = 0;
        $all = rows($src, "SELECT " . implode(',', array_map(fn($c) => "`$c`", $cols)) . " FROM `$s` ORDER BY id");
        if ($all) {
            $ph = '(' . implode(',', array_fill(0, count($cols), '?')) . ')';
            $ins = $tgt->prepare("INSERT INTO `$t` (" . implode(',', array_map(fn($c) => "`$c`", $cols)) . ") VALUES $ph");
            foreach (array_chunk($all, 500) as $chunk) {
                foreach ($chunk as $r) { $ins->execute(array_map(fn($c) => $r[$c], $cols)); $n++; }
            }
        }
        $chk = (int) one($tgt, "SELECT COUNT(*) FROM `$t`");
        printf("  %-22s -> %-15s %d rows %s\n", $s, $t, $n, $chk === $n ? 'OK' : "MISMATCH ($chk)");
    }

    $tmpPassword = bin2hex(random_bytes(6));
    $insU = $tgt->prepare('INSERT INTO users (id, username, password, is_admin, created_at, updated_at) VALUES (?,?,?,?,?,?)');
    $n = 0; $tmp = [];
    foreach (rows($src, 'SELECT id, username, is_superuser, last_login FROM auth_user ORDER BY id') as $u) {
        $plain = $pwList[$u['username']] ?? null;
        if ($plain === null) { $plain = $tmpPassword; $tmp[] = $u['username']; }
        $now = $u['last_login'] ?? date('Y-m-d H:i:s');
        $insU->execute([$u['id'], $u['username'], password_hash($plain, PASSWORD_BCRYPT), $u['is_superuser'] ? 1 : 0, $now, $now]);
        $n++;
    }
    $chk = (int) one($tgt, 'SELECT COUNT(*) FROM users');
    printf("  %-22s -> %-15s %d rows %s\n", 'auth_user', 'users', $n, $chk === $n ? 'OK' : "MISMATCH ($chk)");
    if ($tmp) {
        echo "\n  WARNING: no plaintext password given for: " . implode(', ', $tmp) . "\n";
        echo "  They were all set to temporary password: $tmpPassword\n";
        echo "  Change them immediately (or re-run with --passwords=\"email:pass,...\").\n";
    }
    $tgt->commit();
} catch (Throwable $e) {
    $tgt->rollBack();
    echo "\nFAILED, rolled back: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n== Target (Laravel) ==\n";
$bad = 0;
foreach ($map as $t => [$s, $cols]) {
    $c = (int) one($tgt, "SELECT COUNT(*) FROM `$t`");
    $ok = $c === $srcCounts[$t];
    printf("  %-15s %6d / %6d %s\n", $t, $c, $srcCounts[$t], $ok ? 'OK' : 'FAIL');
    if (!$ok) $bad++;
}
$orphL = (int) one($tgt, 'SELECT COUNT(*) FROM livraisons l LEFT JOIN camions c ON c.id=l.camion_id WHERE c.id IS NULL');
$orphS = (int) one($tgt, 'SELECT COUNT(*) FROM statut_camions s LEFT JOIN camions c ON c.id=s.camion_id WHERE c.id IS NULL');
$dupS = (int) one($tgt, 'SELECT COUNT(*) FROM (SELECT camion_id, date FROM statut_camions WHERE date IS NOT NULL GROUP BY camion_id, date HAVING COUNT(*)>1) x');
echo "  orphan livraisons: $orphL | orphan statuts: $orphS | duplicate (camion,date): $dupS\n";
if ($orphL || $orphS || $dupS) $bad++;
foreach ($map as $t => [$s, $cols]) {
    $m = (int) one($tgt, "SELECT COALESCE(MAX(id),0) FROM `$t`");
    printf("  next %s id: %d\n", $t, $m + 1);
}
echo $bad ? "\nRESULT: CHECKS FAILED\n" : "\nRESULT: MIGRATION OK\n";
exit($bad ? 1 : 0);
