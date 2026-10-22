<form id="editStatutForm" method="post" action="{{ route('modifier_statut_camion', $camion->id, [], false) }}?date={{ $date }}">
    @csrf
    <input type="hidden" name="date" value="{{ $date }}">
    <h1> Camion {{ $camion->numero }}</h1>
    @if($date)
        <p>Pour la date : {{ $date }}</p>
    @endif
    <div class="mb-3">
        <label for="statut" class="form-label">Statut du camion</label>
        <select class="form-select" id="statut" name="statut" required>
            @foreach($STATUT_CHOICES as $value => $display)
                <option value="{{ $value }}">{{ $display }}</option>
            @endforeach
        </select>
    </div>
    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Annuler</button>
        <button type="submit" class="btn btn-primary">Enregistrer</button>
    </div>
</form>
