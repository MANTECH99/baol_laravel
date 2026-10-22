@extends('layouts.base')

@section('content')
<div class="container-fluid mt-5">
<h2>Rapports Mensuels</h2>

<form method="get" class="d-flex align-items-center gap-2">
    <label for="mois" class="me-2">Filtrer par mois :</label>
    <input type="month" name="mois" id="mois" class="form-control w-auto" value="{{ request('mois') }}">
    <button type="submit" class="btn btn-primary">Filtrer</button>
</form>

<table class="table mt-3">
    <thead>
        <tr>
            <th>Mois</th>
            <th>Fichier</th>
            <th>Date d'ajout</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach($rapports as $rapport)
        <tr>
            <td>{{ django_date($rapport->mois) }}</td>
            <td>{{ $rapport->fichier }}</td>
            <td>{{ $rapport->date_ajout->format('d M Y') }}</td>
            <td><a href="{{ route('telecharger_rapport', $rapport->id) }}" class="btn btn-success">Télécharger</a>
            <a href="{{ route('supprimer_rapport', $rapport->id) }}"
               class="btn btn-danger"
               onclick="return confirmSuppression(event, '{{ $rapport->mois->format('F Y') }}');">
                Supprimer
            </a>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<a href="{{ route('ajouter_rapport') }}" class="btn btn-primary">Ajouter un Rapport</a>
<script>
function confirmSuppression(event, mois) {
    event.preventDefault();
    if (confirm(`Voulez-vous vraiment supprimer le rapport de ${mois} ?`)) {
        window.location.href = event.target.href;
    }
}
</script>
</div>
@endsection
