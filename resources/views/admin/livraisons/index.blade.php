@extends('layouts.admin')

@section('title', 'Sélectionner livraison à modifier')
@section('breadcrumbs')
    <span class="separator">›</span> <a href="{{ route('admin.dashboard', [], false) }}">Site administration</a>
    <span class="separator">›</span> Livraisons
@endsection

@section('content')
    <div class="object-tools">
        <a href="{{ route('admin.livraisons.create', [], false) }}">Ajouter une livraison +</a>
    </div>

    <div class="module">
        <h2>Livraisons</h2>
        <div class="result-count">{{ $livraisons->total() }} livraison{{ $livraisons->total() > 1 ? 's' : '' }}</div>
        <table>
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Date</th>
                    <th scope="col">Camion</th>
                    <th scope="col">Tonnage</th>
                    <th scope="col">Prix un.</th>
                    <th scope="col">Qté</th>
                    <th scope="col">Montant</th>
                    <th scope="col">Statut</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($livraisons as $livraison)
                <tr>
                    <td>{{ $livraison->id }}</td>
                    <td>{{ $livraison->date->format('d/m/Y') }}</td>
                    <td>{{ $livraison->camion->numero ?? '' }}</td>
                    <td>{{ $livraison->tonnage }}</td>
                    <td>{{ $livraison->prix_unitaire }}</td>
                    <td>{{ $livraison->quantite }}</td>
                    <td>{{ $livraison->montant }}</td>
                    <td>{{ $livraison->statut_display }}</td>
                    <td>
                        <a href="{{ route('admin.livraisons.edit', $livraison->id, false) }}" class="changelink">Modifier</a>
                        &nbsp;|&nbsp;
                        <form action="{{ route('admin.livraisons.destroy', $livraison->id, false) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer cette livraison ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="deletelink">Supprimer</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" style="text-align:center;color:#666;">Aucune livraison.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="paginator">{{ $livraisons->links() }}</div>
    </div>
@endsection
