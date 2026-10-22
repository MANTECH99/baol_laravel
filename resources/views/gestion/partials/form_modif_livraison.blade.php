<style>
    .form-group {
        display: flex;
        align-items: center;
        margin-bottom: 15px;
    }
    .form-group i {
        margin-right: 10px;
        font-size: 20px;
        color: #007bff;
        width: 30px;
        text-align: center;
    }
    .form-group p {
        margin: 0;
        flex: 1;
    }
    .form-group input,
    .form-group select,
    .form-group textarea {
        width: 100%;
        padding: 8px;
        margin-top: 5px;
    }
</style>
<div class="mb-4">
    <h5>Pour le camion : <strong style="color:#0d6efd">{{ $livraison->camion->numero ?? '' }}</strong></h5>
    <h5>Pour la date : <strong style="color:#0d6efd">{{ $livraison->date->format('d/m/Y') }}</strong></h5>
</div>
<form method="POST" id="editLivraisonForm" action="{{ route('modifier_livraison', $livraison->id, [], false) }}">
    @csrf
    <div class="mb-3">
        <div class="form-group">
            <i class="fas fa-weight-hanging"></i>
            <input type="number" step="0.01" name="tonnage" class="form-control" value="{{ old('tonnage', $livraison->tonnage) }}" required>
        </div>
        <div class="form-group">
            <i class="fas fa-sort-numeric-up"></i>
            <input type="number" name="quantite" class="form-control" value="{{ old('quantite', $livraison->quantite) }}" required>
        </div>
        <div class="form-group">
            <i class="fas fa-recycle"></i>
            <input type="text" name="chiffonage" class="form-control" value="{{ old('chiffonage', $livraison->chiffonage) }}">
        </div>
        <div class="form-group">
            <i class="fas fa-file-alt"></i>
            <input type="text" name="numero_bl" class="form-control" value="{{ old('numero_bl', $livraison->numero_bl) }}">
        </div>
        <div class="form-group">
            <i class="fas fa-info-circle"></i>
            <select name="statut" class="form-control" required>
                <option value="enregistré" {{ $livraison->statut == 'enregistré' ? 'selected' : '' }}>Enregistré</option>
            </select>
        </div>
        <div class="form-group">
            <i class="fas fa-check-circle"></i>
            <select name="verification" class="form-control" required>
                <option value="non_verifié" {{ $livraison->verification == 'non_verifié' ? 'selected' : '' }}>Non vérifié</option>
                <option value="verifié" {{ $livraison->verification == 'verifié' ? 'selected' : '' }}>Vérifié</option>
            </select>
        </div>
    </div>
    <div class="text-center">
        <button type="submit" class="btn btn-primary">Enregistrer</button>
    </div>
</form>
