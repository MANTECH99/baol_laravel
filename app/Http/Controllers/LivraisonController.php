<?php

namespace App\Http\Controllers;

use App\Models\Camion;
use App\Models\Livraison;
use App\Models\StatutCamion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Validator;

class LivraisonController extends Controller
{
    public function liste(Request $request)
    {
        $filterType = $request->query('filter_type');
        $selectedDate = $request->query('date', Carbon::today()->format('Y-m-d'));
        $selectedMonth = $request->query('month', Carbon::today()->format('Y-m'));
        $selectedCamionId = $request->query('camion');
        $tonnageFilter = $request->query('tonnage_filter');

        $selectedDateObj = null;
        $dateRange = [];

        $livraisons = Livraison::query();

        if ($filterType === 'date') {
            try {
                $selectedDateObj = Carbon::createFromFormat('Y-m-d', $selectedDate)->startOfDay();
            } catch (\Exception $e) {
                $selectedDateObj = Carbon::today();
            }
            $dateRange = [$selectedDateObj];
            $livraisons->whereDate('date', $selectedDateObj);
            $selectedMonth = null;
        } elseif ($filterType === 'month') {
            try {
                [$y, $m] = explode('-', $selectedMonth);
                $selectedDateObj = Carbon::create((int) $y, (int) $m, 1);
                $lastDay = $selectedDateObj->daysInMonth;
                $dateRange = [];
                for ($d = 1; $d <= $lastDay; $d++) {
                    $dateRange[] = Carbon::create((int) $y, (int) $m, $d);
                }
                $livraisons->whereYear('date', (int) $y)->whereMonth('date', (int) $m);
            } catch (\Exception $e) {
                $selectedDateObj = Carbon::today();
                $livraisons->whereDate('date', $selectedDateObj);
                $dateRange = [$selectedDateObj];
            }
            $selectedDate = null;
        } else {
            $selectedDateObj = Carbon::today();
            $selectedDate = $selectedDateObj->format('Y-m-d');
            $dateRange = [$selectedDateObj];
            $livraisons->whereDate('date', $selectedDateObj);
            $selectedMonth = null;
        }

        if ($selectedCamionId && $selectedCamionId !== 'None') {
            $livraisons->where('camion_id', $selectedCamionId);
        }

        if ($tonnageFilter === 'lt5') {
            $livraisons->where('tonnage', '<', 5);
        }

        $livraisons = $livraisons->orderByDesc('date')->get();

        $livraisonsDict = [];
        foreach ($livraisons as $l) {
            $livraisonsDict[$l->camion_id][$l->date->format('Y-m-d')][] = $l;
        }

        $camions = Camion::all();

        $statutsQs = StatutCamion::whereIn('date', array_map(fn ($d) => $d->format('Y-m-d'), $dateRange))->get();
        $statutsDict = [];
        foreach ($statutsQs as $s) {
            $statutsDict[$s->camion_id][$s->date ? $s->date->format('Y-m-d') : null] = $s->statut_display;
        }

        $showCamionPagination = $filterType === 'month';
        if ($showCamionPagination) {
            $page = max(1, (int) $request->query('page', 1));
            $pageItems = $camions->forPage($page, 1)->values();
            $currentCamions = $pageItems;
            $paginator = new Paginator($pageItems, 1, $page, ['path' => $request->url(), 'query' => $request->query()]);
        } else {
            $paginator = null;
            $currentCamions = $camions;
        }

        $livraisonsParCamionEtDate = [];
        $statutsParDate = [];
        foreach ($currentCamions as $camion) {
            $livraisonsParCamionEtDate[$camion->id] = [];
            $statutsParDate[$camion->id] = [];
            foreach ($dateRange as $day) {
                $dayKey = $day->format('Y-m-d');
                $livraisonsParCamionEtDate[$camion->id][$dayKey] = $livraisonsDict[$camion->id][$dayKey] ?? null;
                $statutsParDate[$camion->id][$dayKey] = $statutsDict[$camion->id][$dayKey] ?? 'En attente';
            }
        }

        return view('livraisons', [
            'page_obj' => $paginator,
            'current_camions' => $currentCamions,
            'all_camions' => $camions,
            'show_camion_pagination' => $showCamionPagination,
            'statuts_par_date' => $statutsParDate,
            'livraisons_par_camion_et_date' => $livraisonsParCamionEtDate,
            'selected_date' => $selectedDate,
            'selected_month' => $selectedMonth,
            'selected_camion_id' => $selectedCamionId,
            'selected_date_obj' => $selectedDateObj,
            'filter_type' => $filterType,
            'date_range' => $dateRange,
            'tonnage_filter' => $tonnageFilter,
            'camions' => $camions,
        ]);
    }

    public function ajouter(Request $request)
    {
        $dateParam = $request->query('date');
        $camionId = $request->query('camion');

        try {
            $selectedDate = $dateParam ? Carbon::createFromFormat('Y-m-d', $dateParam)->startOfDay() : null;
        } catch (\Exception $e) {
            $selectedDate = null;
        }

        $camion = $camionId ? Camion::findOrFail($camionId) : null;

        if ($request->isMethod('POST')) {
            $validator = Validator::make($request->all(), [
                'tonnage' => 'required|numeric|min:0',
                'quantite' => 'required|integer',
                'chiffonage' => 'nullable|string|max:255',
                'numero_bl' => 'nullable|string|max:50',
                'statut' => 'required|in:enregistré',
                'verification' => 'required|in:verifié,non_verifié',
            ], [
                'tonnage.min' => 'Le tonnage ne peut pas être négatif.',
            ], ['tonnage' => 'Tonnage', 'quantite' => 'Quantite', 'chiffonage' => 'Chiffonage', 'numero_bl' => 'Numero bl', 'statut' => 'Statut', 'verification' => 'Verification']);

            if ($validator->fails()) {
                if ($request->header('X-Requested-With') === 'XMLHttpRequest') {
                    return response()->json([
                        'success' => false,
                        'errors' => $validator->errors()->toArray(),
                    ], 400);
                }

                return view('gestion.ajouter_livraison', [
                    'form' => null,
                    'camion' => $camion,
                    'selected_date' => $selectedDate,
                    'errors' => $validator->errors(),
                ]);
            }

            $livraison = Livraison::create([
                'date' => $selectedDate,
                'camion_id' => $camion?->id,
                'tonnage' => $validator->validated()['tonnage'],
                'quantite' => $validator->validated()['quantite'] ?? 1,
                'chiffonage' => $validator->validated()['chiffonage'] ?? null,
                'numero_bl' => $validator->validated()['numero_bl'] ?? null,
                'statut' => $validator->validated()['statut'] ?? 'enregistré',
                'verification' => $validator->validated()['verification'] ?? 'non_verifié',
            ]);

            if ($request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json([
                    'success' => true,
                    'message' => 'Livraison enregistrée avec succès',
                    'livraison' => [
                        'id' => $livraison->id,
                        'date' => $livraison->date->format('Y-m-d'),
                        'date_display' => $livraison->date->format('d F Y'),
                        'camion_id' => $camion?->id,
                        'camion_numero' => $camion?->numero,
                        'tonnage' => (string) $livraison->tonnage,
                        'prix_unitaire' => (string) $livraison->prix_unitaire,
                        'quantite' => $livraison->quantite,
                        'montant' => (string) $livraison->montant,
                        'chiffonage' => $livraison->chiffonage,
                        'numero_bl' => $livraison->numero_bl,
                        'statut' => $livraison->statut_display,
                    ],
                ]);
            }

            return response()->content('<script>window.top.location.reload(true);</script>');
        }

        return view('gestion.ajouter_livraison', [
            'form' => null,
            'camion' => $camion,
            'selected_date' => $selectedDate,
            'errors' => [],
        ]);
    }

    public function modifier(Request $request, int $id)
    {
        $livraison = Livraison::findOrFail($id);

        if ($request->isMethod('POST')) {
            $validator = Validator::make($request->all(), [
                'tonnage' => 'required|numeric|min:0',
                'quantite' => 'required|integer',
                'chiffonage' => 'nullable|string|max:255',
                'numero_bl' => 'nullable|string|max:50',
                'statut' => 'required|in:enregistré',
                'verification' => 'required|in:verifié,non_verifié',
            ], [
                'tonnage.min' => 'Le tonnage ne peut pas être négatif.',
            ], ['tonnage' => 'Tonnage', 'quantite' => 'Quantite', 'chiffonage' => 'Chiffonage', 'numero_bl' => 'Numero bl', 'statut' => 'Statut', 'verification' => 'Verification']);

            if ($validator->fails()) {
                $request->session()->flashInput($request->all());
                $html = View::make('gestion.partials.form_modif_livraison', [
                    'form' => $livraison,
                    'livraison' => $livraison,
                    'errors' => $validator->errors(),
                ])->render();

                return response()->json(['success' => false, 'html' => $html]);
            }

            $livraison->fill($validator->validated());
            $livraison->save();

            return response()->json(['success' => true]);
        }

        $html = View::make('gestion.partials.form_modif_livraison', ['form' => $livraison, 'livraison' => $livraison])->render();
        return response()->json(['html' => $html]);
    }

    public function supprimer(int $id)
    {
        Livraison::findOrFail($id)->delete();
        return redirect('/livraisons/');
    }

    public function modifierStatut(Request $request, int $camionId)
    {
        $camion = Camion::findOrFail($camionId);
        $date = $request->query('date');

        if ($request->isMethod('POST')) {
            $statut = $request->input('statut');
            StatutCamion::updateOrCreate(
                ['camion_id' => $camion->id, 'date' => $date],
                ['statut' => $statut]
            );

            if ($request->header('X-Requested-With') === 'XMLHttpRequest') {
                return response()->json(['success' => true]);
            }
            return redirect('/livraisons/');
        }

        $context = [
            'camion' => $camion,
            'date' => $date,
            'STATUT_CHOICES' => StatutCamion::STATUT_CHOICES,
        ];

        if ($request->header('X-Requested-With') === 'XMLHttpRequest') {
            return response()->view('gestion.partials.statut_camion_form', $context);
        }
        return response()->view('gestion.modifier_statut_camion', $context);
    }

    public function exporter(Request $request)
    {
        $filterType = $request->query('filter_type');
        $selectedDate = $request->query('date');
        $selectedMonth = $request->query('month');
        $selectedCamionId = $request->query('camion');
        $tonnageFilter = $request->query('tonnage_filter');

        $wb = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $ws = $wb->getActiveSheet();
        $headers = ['Date', 'Camion', 'Numéro Téléphone', 'Tonnage', 'Prix Unitaire', 'Quantité', 'Montant', 'Chiffonage', 'N° BL', 'Statut'];
        $ws->fromArray($headers, null, 'A1');

        $totalTonnage = 0;
        $totalMontant = 0;

        if ($filterType === 'month' && $selectedMonth) {
            try {
                [$year, $month] = array_map('intval', explode('-', $selectedMonth));
                $numDays = Carbon::create($year, $month, 1)->daysInMonth;
                $dateRange = [];
                for ($d = 1; $d <= $numDays; $d++) {
                    $dateRange[] = Carbon::create($year, $month, $d);
                }

                $camions = Camion::query();
                if ($selectedCamionId) {
                    $camions->where('id', $selectedCamionId);
                }
                $camions = $camions->orderBy('numero')->get();

                $livraisonQs = Livraison::whereYear('date', $year)->whereMonth('date', $month);
                if ($selectedCamionId) {
                    $livraisonQs->where('camion_id', $selectedCamionId);
                }
                if ($tonnageFilter === 'lt5') {
                    $livraisonQs->where('tonnage', '<', 5);
                }

                $livraisonsDict = [];
                foreach ($livraisonQs->orderBy('camion_id')->orderBy('date')->get() as $l) {
                    $livraisonsDict[$l->camion_id][$l->date->format('Y-m-d')][] = $l;
                }

                $statutsDict = [];
                foreach (StatutCamion::whereIn('date', array_map(fn ($d) => $d->format('Y-m-d'), $dateRange))->get() as $s) {
                    $statutsDict[$s->camion_id][$s->date ? $s->date->format('Y-m-d') : null] = $s->statut_display;
                }

                $fillColors = [
                    'En panne' => 'FF9999',
                    'Travaille pas' => 'FFF699',
                    'En attente' => 'B4C6E7',
                    'Enregistré' => 'A8E6A3',
                ];

                foreach ($camions as $camion) {
                    foreach ($dateRange as $currentDate) {
                        $dayKey = $currentDate->format('Y-m-d');
                        $livraisons = $livraisonsDict[$camion->id][$dayKey] ?? null;
                        if ($livraisons) {
                            foreach ($livraisons as $l) {
                                $ws->fromArray([
                                    $l->date->format('d/m/Y'),
                                    $l->camion->numero,
                                    $l->camion->telephone,
                                    (float) $l->tonnage,
                                    $l->prix_unitaire,
                                    $l->quantite,
                                    $l->montant,
                                    $l->chiffonage,
                                    $l->numero_bl,
                                    $l->statut_display,
                                ], null, 'A' . ($ws->getHighestRow() + 1));
                                $totalTonnage += (float) $l->tonnage;
                                $totalMontant += $l->montant;
                            }
                        } elseif ($tonnageFilter !== 'lt5') {
                            $statut = $statutsDict[$camion->id][$dayKey] ?? 'En attente';
                            $rowNum = $ws->getHighestRow() + 1;
                            $ws->fromArray([
                                $currentDate->format('d/m/Y'),
                                $camion->numero,
                                $camion->telephone,
                                '', '', '', '', '', '',
                                $statut,
                            ], null, 'A' . $rowNum);
                            $ws->mergeCells('D' . $rowNum . ':I' . $rowNum);
                            $ws->setCellValue('D' . $rowNum, 'Aucune livraison');
                            $ws->getStyle('D' . $rowNum)->getAlignment()->setHorizontal('center')->setVertical('center');
                            $color = $fillColors[$statut] ?? null;
                            if ($color) {
                                $ws->getStyle('J' . $rowNum)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                                    ->getStartColor()->setARGB('FF' . $color);
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                return response('Format de mois invalide');
            }
        } else {
            $livraisons = Livraison::query()->leftJoin('camions', 'livraisons.camion_id', '=', 'camions.id');
            if ($selectedCamionId) {
                $livraisons->where('livraisons.camion_id', $selectedCamionId);
            }
            if ($filterType === 'date' && $selectedDate) {
                try {
                    $dateObj = Carbon::createFromFormat('Y-m-d', $selectedDate)->startOfDay();
                    $livraisons->whereDate('livraisons.date', $dateObj);
                } catch (\Exception $e) {
                    $livraisons = Livraison::none();
                }
            }
            if ($tonnageFilter === 'lt5') {
                $livraisons->where('livraisons.tonnage', '<', 5);
            }

            foreach ($livraisons->orderBy('camions.numero')->orderBy('livraisons.date')->get() as $l) {
                $ws->fromArray([
                    $l->date->format('d/m/Y'),
                    $l->camion->numero,
                    $l->camion->telephone,
                    (float) $l->tonnage,
                    $l->prix_unitaire,
                    $l->quantite,
                    $l->montant,
                    $l->chiffonage,
                    $l->numero_bl,
                    $l->statut_display,
                ], null, 'A' . ($ws->getHighestRow() + 1));
                $totalTonnage += (float) $l->tonnage;
                $totalMontant += $l->montant;
            }
        }

        $lastRow = $ws->getHighestRow() + 2;
        $ws->fromArray(['Total', '', '', $totalTonnage, '', '', $totalMontant, '', '', ''], null, 'A' . $lastRow);

        $filenameParts = [];
        if ($selectedCamionId) {
            $filenameParts[] = "camion_{$selectedCamionId}";
        }
        if ($selectedDate) {
            $filenameParts[] = "date_{$selectedDate}";
        } elseif ($selectedMonth) {
            $filenameParts[] = "mois_{$selectedMonth}";
        }
        if ($tonnageFilter === 'lt5') {
            $filenameParts[] = 'tonnage_lt5';
        }
        $filename = 'livraisons_' . (implode('_', $filenameParts) ?: 'all') . '.xlsx';

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($wb);
        $tempFile = tempnam(sys_get_temp_dir(), 'baol');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
}
