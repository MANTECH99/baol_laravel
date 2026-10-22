<?php

namespace App\Http\Controllers;

use App\Models\Camion;
use App\Models\Livraison;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $filterType = $request->query('filter_type');
        $selectedDate = $request->query('date', Carbon::today()->format('Y-m-d'));
        $selectedMonth = $request->query('month', Carbon::today()->format('Y-m'));
        $selectedYear = $request->query('year', (string) Carbon::today()->year);
        $selectedCamionId = $request->query('camion');

        $baseQuery = Livraison::query();
        $selectedDateObj = Carbon::today();

        if ($selectedCamionId && $selectedCamionId !== 'None') {
            $baseQuery->where('camion_id', $selectedCamionId);
        }

        if ($filterType === 'date') {
            try {
                $selectedDateObj = Carbon::createFromFormat('Y-m-d', $selectedDate)->startOfDay();
            } catch (\Exception $e) {
                $selectedDateObj = Carbon::today();
            }
            $livraisons = $baseQuery->whereDate('date', $selectedDateObj);
            $selectedMonth = null;
            $selectedYear = null;
        } elseif ($filterType === 'month') {
            try {
                [$y, $m] = explode('-', $selectedMonth);
                $selectedDateObj = Carbon::create((int) $y, (int) $m, 1);
                $livraisons = $baseQuery->whereYear('date', (int) $y)->whereMonth('date', (int) $m);
            } catch (\Exception $e) {
                $selectedDateObj = Carbon::today();
                $livraisons = $baseQuery->whereDate('date', $selectedDateObj);
            }
            $selectedDate = null;
            $selectedYear = null;
        } elseif ($filterType === 'year') {
            try {
                $year = (int) $selectedYear;
            } catch (\Exception $e) {
                $year = Carbon::today()->year;
            }
            $livraisons = $baseQuery->whereYear('date', $year);
            $selectedDate = null;
            $selectedMonth = null;
        } else {
            $livraisons = $baseQuery->whereDate('date', $selectedDateObj);
            $selectedMonth = null;
            $selectedYear = null;
        }

        $stats = (clone $livraisons)->selectRaw('SUM(tonnage) as total_tonnage, SUM(montant) as total_montant')->first();
        $totalTonnage = $stats->total_tonnage ?? 0;
        $totalMontant = $stats->total_montant ?? 0;
        $totalLivraisons = (clone $livraisons)->count();

        $moisLabels = [];
        $tonnageValues = [];
        $montantValues = [];

        if ($filterType === 'year') {
            $y = (int) $selectedYear ?: Carbon::today()->year;
            for ($mois = 1; $mois <= 12; $mois++) {
                $q = (clone $baseQuery)->whereYear('date', $y)->whereMonth('date', $mois)
                    ->selectRaw('SUM(tonnage) as t, SUM(montant) as m')->first();
                $moisLabels[] = "{$mois}/{$y}";
                $tonnageValues[] = (float) ($q->t ?? 0);
                $montantValues[] = (float) ($q->m ?? 0);
            }
        } elseif ($filterType === 'month') {
            try {
                [$y, $m] = explode('-', $selectedMonth);
                $y = (int) $y;
                $m = (int) $m;
                $nbJours = Carbon::create($y, $m, 1)->daysInMonth;
                for ($jour = 1; $jour <= $nbJours; $jour++) {
                    $q = (clone $baseQuery)->whereYear('date', $y)->whereMonth('date', $m)->whereDay('date', $jour)
                        ->selectRaw('SUM(tonnage) as t, SUM(montant) as m')->first();
                    $moisLabels[] = "{$jour}/{$m}";
                    $tonnageValues[] = (float) ($q->t ?? 0);
                    $montantValues[] = (float) ($q->m ?? 0);
                }
            } catch (\Exception $e) {
                // ignore
            }
        } else {
            $moisActuel = Carbon::today()->month;
            $anneeActuelle = Carbon::today()->year;
            for ($i = 0; $i < 6; $i++) {
                $mois = (($moisActuel - $i) % 12) ?: 12;
                $annee = ($moisActuel - $i > 0) ? $anneeActuelle : $anneeActuelle - 1;
                $q = (clone $baseQuery)->whereYear('date', $annee)->whereMonth('date', $mois)
                    ->selectRaw('SUM(tonnage) as t, SUM(montant) as m')->first();
                $moisLabels[] = "{$mois}/{$annee}";
                $tonnageValues[] = (float) ($q->t ?? 0);
                $montantValues[] = (float) ($q->m ?? 0);
            }
        }

        $livraisonsMois = (clone $livraisons)->with('camion')->orderByDesc('date')->limit(50)
            ->get(['id', 'date', 'camion_id', 'tonnage', 'prix_unitaire', 'quantite', 'montant', 'chiffonage', 'numero_bl', 'statut'])
            ->map(function ($l) {
                return [
                    'date' => $l->date,
                    'camion_numero' => $l->camion->numero ?? '',
                    'camion_telephone' => $l->camion->telephone ?? '',
                    'tonnage' => $l->tonnage,
                    'prix_unitaire' => $l->prix_unitaire,
                    'quantite' => $l->quantite,
                    'montant' => $l->montant,
                    'chiffonage' => $l->chiffonage,
                    'numero_bl' => $l->numero_bl,
                    'statut' => $l->statut,
                ];
            });

        return view('dashboard', [
            'selected_date' => $selectedDate,
            'selected_month' => $selectedMonth,
            'selected_year' => $selectedYear,
            'selected_camion_id' => $selectedCamionId,
            'selected_date_obj' => $selectedDateObj,
            'filter_type' => $filterType,
            'total_tonnage' => $totalTonnage,
            'total_montant' => $totalMontant,
            'total_livraisons' => $totalLivraisons,
            'mois_labels' => json_encode(array_reverse($moisLabels)),
            'tonnage_values' => json_encode(array_reverse($tonnageValues)),
            'montant_values' => json_encode(array_reverse($montantValues)),
            'livraisons_mois' => $livraisonsMois,
            'last_five_years' => collect(range(0, 4))->map(fn ($i) => Carbon::today()->year - $i),
            'camions' => Camion::all(),
        ]);
    }
}
