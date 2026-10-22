<?php

namespace App\Http\Controllers;

use App\Models\Rapport;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RapportController extends Controller
{
    public function index(Request $request)
    {
        $moisSelectionne = $request->query('mois');
        $query = Rapport::query();

        if ($moisSelectionne) {
            [$y, $m] = explode('-', $moisSelectionne);
            $query->whereMonth('mois', (int) $m)->whereYear('mois', (int) $y);
        }

        return view('rapports.rapports', ['rapports' => $query->get()]);
    }

    public function telecharger(int $id)
    {
        $rapport = Rapport::findOrFail($id);
        return Storage::disk('public')->download($rapport->fichier, basename($rapport->fichier));
    }

    public function ajouter(Request $request)
    {
        if ($request->isMethod('POST')) {
            $moisStr = $request->input('mois');

            if (!$moisStr) {
                return response()->json(['error' => "Le champ 'mois' est vide"], 400);
            }

            try {
                $moisCorrige = Carbon::createFromFormat('Y-m-d', $moisStr . '-01')->startOfDay();
            } catch (\Exception $e) {
                return response()->json(['error' => 'Erreur conversion date : ' . $e->getMessage()], 400);
            }

            $request->merge(['mois' => $moisCorrige->format('Y-m-d')]);

            $validated = $request->validate([
                'mois' => 'required|date',
                'fichier' => 'required|file',
            ]);

            if ($request->hasFile('fichier')) {
                $file = $request->file('fichier');
                $name = str_replace(' ', '_', trim($file->getClientOriginalName()));
                $name = preg_replace('/[^\w.\-]/u', '', $name);
                if ($name === '' || $name === '.' || $name === '..') {
                    $name = 'rapport_' . time();
                }
                $ext = pathinfo($name, PATHINFO_EXTENSION);
                $base = $ext !== '' ? substr($name, 0, -(strlen($ext) + 1)) : $name;
                $candidate = $name;
                while (Storage::disk('public')->exists('rapports/' . $candidate)) {
                    $rand = \Illuminate\Support\Str::random(7);
                    $candidate = $ext !== '' ? "{$base}_{$rand}.{$ext}" : "{$base}_{$rand}";
                }
                $path = $file->storeAs('rapports', $candidate, 'public');
            }

            Rapport::create([
                'mois' => $moisCorrige,
                'fichier' => $path ?? '',
            ]);

            return redirect('/rapports/');
        }

        return view('rapports.ajouter_rapport');
    }

    public function supprimer(int $id)
    {
        Rapport::findOrFail($id)->delete();
        return redirect('/rapports/');
    }
}
