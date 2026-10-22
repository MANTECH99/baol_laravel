<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Camion;
use App\Models\Livraison;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct()
    {
        // middleware check done in routes
    }

    public function dashboard()
    {
        return view('admin.dashboard', [
            'camions_count' => Camion::count(),
            'livraisons_count' => Livraison::count(),
        ]);
    }

    public function camionsIndex()
    {
        return view('admin.camions.index', ['camions' => Camion::orderBy('numero')->get()]);
    }

    public function camionsCreate()
    {
        return view('admin.camions.form', ['camion' => null]);
    }

    public function camionsStore(Request $request)
    {
        $validated = $request->validate([
            'numero' => 'required|string|max:20|unique:camions,numero',
            'telephone' => 'required|string|max:15|unique:camions,telephone',
        ]);
        $camion = Camion::create($validated);

        if ($request->has('_addanother')) {
            return redirect('/admin/camions/create')->with('success', "Le camion « {$camion->numero} » a été ajouté avec succès.");
        }

        return redirect('/admin/camions')->with('success', "Le camion « {$camion->numero} » a été ajouté avec succès.");
    }

    public function camionsEdit(int $id)
    {
        return view('admin.camions.form', ['camion' => Camion::findOrFail($id)]);
    }

    public function camionsUpdate(Request $request, int $id)
    {
        $camion = Camion::findOrFail($id);
        $validated = $request->validate([
            'numero' => 'required|string|max:20|unique:camions,numero,' . $id,
            'telephone' => 'required|string|max:15|unique:camions,telephone,' . $id,
        ]);
        $camion->update($validated);

        if ($request->has('_addanother')) {
            return redirect('/admin/camions/create')->with('success', "Le camion « {$camion->numero} » a été modifié avec succès.");
        }

        return redirect('/admin/camions')->with('success', "Le camion « {$camion->numero} » a été modifié avec succès.");
    }

    public function camionsDestroy(int $id)
    {
        $camion = Camion::findOrFail($id);
        $numero = $camion->numero;
        $camion->delete();
        return redirect('/admin/camions')->with('success', "Le camion « {$numero} » a été supprimé avec succès.");
    }

    public function livraisonsIndex()
    {
        return view('admin.livraisons.index', [
            'livraisons' => Livraison::with('camion')->orderByDesc('date')->orderBy('id')->paginate(50),
        ]);
    }

    public function livraisonsCreate()
    {
        return view('admin.livraisons.form', [
            'livraison' => null,
            'camions' => Camion::orderBy('numero')->get(),
        ]);
    }

    public function livraisonsStore(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'camion_id' => 'required|exists:camions,id',
            'tonnage' => 'required|numeric|min:0',
            'quantite' => 'required|integer',
            'chiffonage' => 'nullable|string|max:255',
            'numero_bl' => 'nullable|string|max:50',
            'statut' => 'required|in:enregistré',
            'verification' => 'required|in:verifié,non_verifié',
        ]);
        Livraison::create($validated);

        if ($request->has('_addanother')) {
            return redirect('/admin/livraisons/create')->with('success', 'La livraison a été ajoutée avec succès.');
        }

        return redirect('/admin/livraisons')->with('success', 'La livraison a été ajoutée avec succès.');
    }

    public function livraisonsEdit(int $id)
    {
        return view('admin.livraisons.form', [
            'livraison' => Livraison::findOrFail($id),
            'camions' => Camion::orderBy('numero')->get(),
        ]);
    }

    public function livraisonsUpdate(Request $request, int $id)
    {
        $livraison = Livraison::findOrFail($id);
        $validated = $request->validate([
            'date' => 'required|date',
            'camion_id' => 'required|exists:camions,id',
            'tonnage' => 'required|numeric|min:0',
            'quantite' => 'required|integer',
            'chiffonage' => 'nullable|string|max:255',
            'numero_bl' => 'nullable|string|max:50',
            'statut' => 'required|in:enregistré',
            'verification' => 'required|in:verifié,non_verifié',
        ]);
        $livraison->update($validated);

        if ($request->has('_addanother')) {
            return redirect('/admin/livraisons/create')->with('success', 'La livraison a été modifiée avec succès.');
        }

        return redirect('/admin/livraisons')->with('success', 'La livraison a été modifiée avec succès.');
    }

    public function livraisonsDestroy(int $id)
    {
        Livraison::findOrFail($id)->delete();
        return redirect('/admin/livraisons')->with('success', 'La livraison a été supprimée avec succès.');
    }
}
