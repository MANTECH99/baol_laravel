@extends('layouts.admin')

@section('title', $livraison ? 'Modifier livraison' : 'Ajouter livraison')
@section('breadcrumbs')
    <span class="separator">›</span> <a href="{{ route('admin.dashboard', [], false) }}">Site administration</a>
    <span class="separator">›</span> <a href="{{ route('admin.livraisons.index', [], false) }}">Livraisons</a>
    <span class="separator">›</span> {{ $livraison ? 'Livraison n° ' . $livraison->id : 'Ajouter une livraison' }}
@endsection

@section('content')
    <form method="POST" action="{{ $livraison ? route('admin.livraisons.update', $livraison->id, false) : route('admin.livraisons.store', [], false) }}">
        @csrf
        @if($livraison) @method('PUT') @endif

        <div class="module">
            <h2>{{ $livraison ? 'Modifier livraison' : 'Ajouter livraison' }}</h2>

            <div class="form-module">
                @if($errors->any())
                    <ul class="errorlist">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                @endif

                <div class="form-row">
                    <label for="id_date">Date :</label>
                    <input type="date" name="date" id="id_date" value="{{ old('date', $livraison?->date?->format('Y-m-d') ?? '') }}" required>
                </div>

                <div class="form-row">
                    <label for="id_camion_id">Camion :</label>
                    <select name="camion_id" id="id_camion_id" required>
                        @foreach($camions as $camion)
                            <option value="{{ $camion->id }}" {{ old('camion_id', $livraison->camion_id ?? '') == $camion->id ? 'selected' : '' }}>
                                {{ $camion->numero }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-row">
                    <label for="id_tonnage">Tonnage :</label>
                    <input type="number" step="0.01" name="tonnage" id="id_tonnage" value="{{ old('tonnage', $livraison->tonnage ?? '') }}" required>
                    <div class="help">Prix unitaire et montant calculés automatiquement selon les paliers.</div>
                </div>

                <div class="form-row">
                    <label for="id_quantite">Quantité :</label>
                    <input type="number" name="quantite" id="id_quantite" value="{{ old('quantite', $livraison->quantite ?? 1) }}" required>
                </div>

                <div class="form-row">
                    <label for="id_chiffonage">Chiffonage :</label>
                    <input type="text" name="chiffonage" id="id_chiffonage" value="{{ old('chiffonage', $livraison->chiffonage ?? '') }}">
                </div>

                <div class="form-row">
                    <label for="id_numero_bl">N° BL :</label>
                    <input type="text" name="numero_bl" id="id_numero_bl" value="{{ old('numero_bl', $livraison->numero_bl ?? '') }}">
                </div>

                <div class="form-row">
                    <label for="id_statut">Statut :</label>
                    <select name="statut" id="id_statut" required>
                        <option value="enregistré" {{ old('statut', $livraison->statut ?? '') == 'enregistré' ? 'selected' : '' }}>Enregistré</option>
                    </select>
                </div>

                <div class="form-row">
                    <label for="id_verification">Vérification :</label>
                    <select name="verification" id="id_verification" required>
                        <option value="non_verifié" {{ old('verification', $livraison->verification ?? '') == 'non_verifié' ? 'selected' : '' }}>Non vérifié</option>
                        <option value="verifié" {{ old('verification', $livraison->verification ?? '') == 'verifié' ? 'selected' : '' }}>Vérifié</option>
                    </select>
                </div>
            </div>

            <div class="submit-row">
                @if($livraison)
                    <a href="{{ route('admin.livraisons.destroy', $livraison->id, false) }}"
                       class="deletelink"
                       onclick="event.preventDefault(); document.getElementById('delete-form').submit();">Supprimer</a>
                @endif
                <a href="{{ route('admin.livraisons.index', [], false) }}" class="button cancel">Annuler</a>
                <input type="submit" value="Enregistrer">
                <input type="submit" name="_addanother" value="Enregistrer et ajouter un autre">
            </div>
        </div>
    </form>

    @if($livraison)
    <form id="delete-form" action="{{ route('admin.livraisons.destroy', $livraison->id, false) }}" method="POST" style="display:none;">
        @csrf @method('DELETE')
    </form>
    @endif
@endsection
