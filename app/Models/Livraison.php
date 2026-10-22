<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Livraison extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'date', 'camion_id', 'tonnage', 'prix_unitaire', 'quantite',
        'montant', 'chiffonage', 'numero_bl', 'statut', 'verification',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'tonnage' => 'decimal:2',
        'prix_unitaire' => 'integer',
        'quantite' => 'integer',
        'montant' => 'integer',
    ];

    protected static function booted(): void
    {
        static::saving(function (Livraison $livraison) {
            $t = (float) $livraison->tonnage;
            if ($t < 5) {
                $livraison->prix_unitaire = 0;
            } elseif ($t < 8) {
                $livraison->prix_unitaire = 2500;
            } elseif ($t < 10) {
                $livraison->prix_unitaire = 3500;
            } elseif ($t <= 14.5) {
                $livraison->prix_unitaire = 5000;
            } else {
                $livraison->prix_unitaire = 7500;
            }
            $livraison->montant = $livraison->prix_unitaire * $livraison->quantite;
        });
    }

    public function camion()
    {
        return $this->belongsTo(Camion::class);
    }

    public const STATUTS = ['enregistré' => 'Enregistré'];

    public const VERIFICATIONS = ['verifié' => 'Vérifié', 'non_verifié' => 'Non vérifié'];

    public function getStatutDisplayAttribute(): string
    {
        return self::STATUTS[$this->statut] ?? $this->statut;
    }

    public function getVerificationDisplayAttribute(): string
    {
        return self::VERIFICATIONS[$this->verification] ?? $this->verification;
    }
}
