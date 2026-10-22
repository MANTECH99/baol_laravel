@extends('layouts.base')

@section('content')
<div class="container-fluid mt-5">
<h2>Ajouter un Rapport</h2>

<form method="post" enctype="multipart/form-data" class="p-4 shadow bg-white rounded" action="{{ route('ajouter_rapport', [], false) }}">
    @csrf
    <div class="mb-3">
        <label class="form-label">Sélectionner le mois :</label>
        <div style="max-width: 200px;">
            <input type="month" name="mois" class="form-control" value="{{ old('mois') }}">
        </div><br>
    <div class="mb-3">
        <label class="form-label">Fichier :</label>
        <input type="file" name="fichier" class="form-control" required>
    </div>
    @if($errors->any())
        <div style="color: red; font-size: 14px; margin-bottom: 10px;">
            {{ $errors->first() }}
        </div>
    @endif
    <button type="submit" class="btn btn-primary">Ajouter</button>
</form>

<a href="{{ route('liste_rapports', [], false) }}" class="btn btn-secondary">Retour</a>
</div>
@endsection
