@extends('layouts.admin')

@section('title', 'Accueil')
@section('breadcrumbs')
    <span class="separator">›</span> Site administration
@endsection

@section('content')
    <h1>Site administration</h1>

    <div class="module">
        <h2><a href="{{ route('admin.dashboard') }}">Baol Distributions</a></h2>
        <table id="change-history">
            <thead>
                <tr>
                    <th scope="col">Modèle</th>
                    <th scope="col">&nbsp;</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <th scope="row">
                        <a href="{{ route('admin.camions.index') }}">Camions</a>
                    </th>
                    <td>
                        <a href="{{ route('admin.camions.create') }}" class="addlink">Ajouter</a>
                        <a href="{{ route('admin.camions.index') }}" class="changelink">Modifier</a>
                        <div style="color:#666;font-size:11px;">{{ $camions_count }} objet{{ $camions_count > 1 ? 's' : '' }}</div>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <a href="{{ route('admin.livraisons.index') }}">Livraisons</a>
                    </th>
                    <td>
                        <a href="{{ route('admin.livraisons.create') }}" class="addlink">Ajouter</a>
                        <a href="{{ route('admin.livraisons.index') }}" class="changelink">Modifier</a>
                        <div style="color:#666;font-size:11px;">{{ $livraisons_count }} objet{{ $livraisons_count > 1 ? 's' : '' }}</div>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="module">
        <h2>Administration récente</h2>
        <div class="module-caption">
            Aucune action récente à afficher.
        </div>
    </div>
@endsection
