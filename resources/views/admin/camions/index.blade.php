@extends('layouts.admin')

@section('title', 'Sélectionner camion à modifier')
@section('breadcrumbs')
    <span class="separator">›</span> <a href="{{ route('admin.dashboard') }}">Site administration</a>
    <span class="separator">›</span> Camions
@endsection

@section('content')
    <div class="object-tools">
        <a href="{{ route('admin.camions.create') }}">Ajouter un camion +</a>
    </div>

    <div class="module">
        <h2>Camions</h2>
        <div class="result-count">{{ $camions->count() }} camion{{ $camions->count() > 1 ? 's' : '' }}</div>
        <table>
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Numéro</th>
                    <th scope="col">Téléphone</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($camions as $camion)
                <tr>
                    <td>{{ $camion->id }}</td>
                    <td>
                        <a href="{{ route('admin.camions.edit', $camion->id) }}" class="changelink">{{ $camion->numero }}</a>
                    </td>
                    <td>{{ $camion->telephone }}</td>
                    <td>
                        <a href="{{ route('admin.camions.edit', $camion->id) }}" class="changelink">Modifier</a>
                        &nbsp;|&nbsp;
                        <form action="{{ route('admin.camions.destroy', $camion->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Supprimer ce camion ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="deletelink">Supprimer</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align:center;color:#666;">Aucun camion.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
