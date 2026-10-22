<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<div class="livraison-form-container">
    <div class="form-header mb-3">
        <div class="d-flex align-items-center gap-3 mb-3">
            <h5 class="text-primary mb-0"><i class="fas fa-truck-loading me-2"></i>Nouvelle livraison</h5>
            <span class="badge bg-primary bg-opacity-10 text-primary p-2 fs-6">
                <i class="fas fa-truck me-2"></i>
                <strong>Camion :</strong> {{ $camion->numero ?? '' }}
            </span>
            <span class="badge bg-info bg-opacity-10 text-info p-2 fs-6">
                <i class="far fa-calendar me-2"></i>
                <strong>Date :</strong> {{ $selected_date ? $selected_date->format('j F Y') : '' }}
            </span>
        </div>
    </div>

    <form id="livraisonForm" method="post">
        @csrf

        <div class="form-fields">
            <div class="form-field">
                <label for="id_tonnage"><i class="fas fa-weight-hanging me-1"></i>Tonnage</label>
                <input type="number" step="0.01" name="tonnage" id="id_tonnage" class="form-control" required>
                <div class="error text-danger" id="error_tonnage" style="display:none;"></div>
            </div>
            <div class="form-field">
                <label for="id_quantite"><i class="fas fa-boxes me-1"></i>Quantite</label>
                <input type="number" name="quantite" id="id_quantite" class="form-control" value="1" required>
                <div class="error text-danger" id="error_quantite" style="display:none;"></div>
            </div>
            <div class="form-field">
                <label for="id_chiffonage"><i class="fas fa-recycle me-1"></i>Chiffonage</label>
                <input type="text" name="chiffonage" id="id_chiffonage" class="form-control">
                <div class="error text-danger" id="error_chiffonage" style="display:none;"></div>
            </div>
            <div class="form-field">
                <label for="id_numero_bl"><i class="fas fa-barcode me-1"></i>Numero bl</label>
                <input type="text" name="numero_bl" id="id_numero_bl" class="form-control">
                <div class="error text-danger" id="error_numero_bl" style="display:none;"></div>
            </div>
            <div class="form-field">
                <label for="id_statut"><i class="fas fa-info-circle me-1"></i>Statut</label>
                <select name="statut" id="id_statut" class="form-control" required>
                    <option value="enregistré">Enregistré</option>
                </select>
                <div class="error text-danger" id="error_statut" style="display:none;"></div>
            </div>
        </div>
<br>
        <div class="form-field">
            <label for="id_verification"><i class="fas fa-check-circle me-1"></i>Verification</label>
            <select name="verification" id="id_verification" class="form-control" required>
                <option value="non_verifié">Non vérifié</option>
                <option value="verifié">Vérifié</option>
            </select>
            <div class="error text-danger" id="error_verification" style="display:none;"></div>
        </div>

        <div class="form-actions mt-4 pt-2 border-top">
            <button type="button" class="btn btn-outline-secondary" onclick="fermerIframe()">
                <i class="fas fa-times me-1"></i>Fermer
            </button>
            <button type="submit" class="btn btn-primary" id="submitBtn">
                <i class="fas fa-save me-1"></i>Enregistrer
            </button>
        </div>
    </form>
</div>

<script>
function fermerIframe() {
    const iframe = window.frameElement;
    if (iframe && iframe.parentNode) {
        iframe.parentNode.removeChild(iframe);
    }
}

$(document).ready(function() {
    $('#livraisonForm').on('submit', function(e) {
        e.preventDefault();
        $('#submitBtn').prop('disabled', true);
        $.ajax({
            url: window.location.href,
            type: 'POST',
            data: $(this).serialize(),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRFToken': $('input[name="_token"]').val()
            },
            success: function(response) {
                if (response.success) {
                    if (window.parent) {
                        window.parent.postMessage({ type: 'RELOAD_PAGE' }, '*');
                    }
                    fermerIframe();
                } else {
                    $.each(response.errors, function(field, errors) {
                        $('#error_' + field).text(errors[0]).show();
                    });
                }
            },
            error: function(xhr) {
                alert("Erreur: " + xhr.statusText);
            },
            complete: function() {
                $('#submitBtn').prop('disabled', false);
            }
        });
    });
});
</script>
<style>
.livraison-form-container {
    padding: 15px;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 5px;
}
.form-header {
    padding-bottom: 10px;
    border-bottom: 1px solid #dee2e6;
    margin-bottom: 15px;
}
.form-fields {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
}
.form-field {
    flex: 1;
    min-width: 200px;
}
.form-field label i {
    width: 20px;
    color: #6c757d;
}
.form-actions {
    margin-top: 20px;
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}
.badge {
    padding: 5px 10px;
    font-weight: normal;
}
</style>
