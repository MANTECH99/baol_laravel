@extends('layouts.admin')

@section('title', $camion ? 'Modifier camion' : 'Ajouter camion')
@section('breadcrumbs')
    <span class="separator">›</span> <a href="{{ route('admin.dashboard', [], false) }}">Site administration</a>
    <span class="separator">›</span> <a href="{{ route('admin.camions.index', [], false) }}">Camions</a>
    <span class="separator">›</span> {{ $camion ? $camion->numero : 'Ajouter un camion' }}
@endsection

@section('content')
    <form method="POST" action="{{ $camion ? route('admin.camions.update', $camion->id, false) : route('admin.camions.store', [], false) }}">
        @csrf
        @if($camion) @method('PUT') @endif

        <div class="module">
            <h2>{{ $camion ? 'Modifier camion' : 'Ajouter camion' }}</h2>

            <div class="form-module">
                @if($errors->any())
                    <ul class="errorlist">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                @endif

                <div class="form-row">
                    <label for="id_numero">Numéro :</label>
                    <input type="text" name="numero" id="id_numero" value="{{ old('numero', $camion->numero ?? '') }}" required>
                    <div class="help">Numéro unique du camion (ex : DK-001).</div>
                    @error('numero')<div style="color:#ba2121;font-size:11px;">{{ $message }}</div>@enderror
                </div>

                <div class="form-row">
                    <label for="id_telephone">Téléphone :</label>
                    <input type="text" name="telephone" id="id_telephone" value="{{ old('telephone', $camion->telephone ?? '') }}" required>
                    <div class="help">Numéro du chauffeur, 15 caractères maximum, unique.</div>
                    @error('telephone')<div style="color:#ba2121;font-size:11px;">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="submit-row">
                @if($camion)
                    <a href="{{ route('admin.camions.destroy', $camion->id, false) }}"
                       class="deletelink"
                       onclick="event.preventDefault(); document.getElementById('delete-form').submit();">Supprimer</a>
                @endif
                <a href="{{ route('admin.camions.index', [], false) }}" class="button cancel">Annuler</a>
                <input type="submit" value="Enregistrer">
                <input type="submit" name="_addanother" value="Enregistrer et ajouter un autre">
            </div>
        </div>
    </form>

    @if($camion)
    <form id="delete-form" action="{{ route('admin.camions.destroy', $camion->id, false) }}" method="POST" style="display:none;">
        @csrf @method('DELETE')
    </form>
    @endif
@endsection
