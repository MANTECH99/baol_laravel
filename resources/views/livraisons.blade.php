@extends('layouts.base')

@section('content')
<style>
    .camion-nav {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        justify-content: center;
        margin: 20px 0;
    }
    .camion-nav a {
        min-width: 70px;
        text-align: center;
    }
</style>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<div class="container-fluid mt-5">
    <h2><i class="fas fa-list text-primary"></i> Liste des Livraisons</h2><br>
<div class="mb-3">
    <label for="camion_filter"><i class="fas fa-truck text-primary"></i> Filtrer par camion :</label>
    <form method="GET" class="d-inline-block">
        <input type="hidden" name="filter_type" value="{{ $filter_type }}">
        <input type="hidden" name="date" value="{{ $selected_date }}">
        <input type="hidden" name="month" value="{{ $selected_month }}">
        <select id="camion_filter" name="camion" class="form-control d-inline-block w-auto" onchange="this.form.submit()">
            <option value="">Tous les camions</option>
            @foreach($camions as $camion)
                <option value="{{ $camion->id }}" {{ $selected_camion_id == $camion->id ? 'selected' : '' }}>
                    {{ $camion->numero }}
                </option>
            @endforeach
        </select>
    </form>
</div>

<div class="mb-3">
    <label for="filter_type"><i class="fas fa-filter text-secondary"></i> Choisir le type de filtre :</label>
    <select id="filter_type" name="filter_type" class="form-control d-inline-block w-auto" onchange="showFilterForm()">
        <option value="">-- Sélectionnez un filtre --</option>
        <option value="date" {{ $filter_type == 'date' ? 'selected' : '' }}>Date personnalisée</option>
        <option value="month" {{ $filter_type == 'month' ? 'selected' : '' }}>Mois spécifique</option>
    </select>&nbsp;&nbsp;&nbsp;

    <form method="GET" class="d-inline-block">
        <input type="hidden" name="filter_type" value="{{ $filter_type }}">
        <input type="hidden" name="date" value="{{ $selected_date }}">
        <input type="hidden" name="month" value="{{ $selected_month }}">
        <input type="hidden" name="camion" value="{{ $selected_camion_id ?? '' }}">
        <input type="hidden" name="tonnage_filter" value="{{ $tonnage_filter == 'lt5' ? 'lt5' : '' }}">
        <button type="submit" name="tonnage_filter" value="lt5" class="btn btn-info ml-2">
            <i class="fas fa-filter text-white"></i> Tonnage < 5
        </button>
    </form>

<a href="{{ route('exporter_livraisons_excel', [], false) }}?filter_type={{ $filter_type }}&date={{ $selected_date }}&month={{ $selected_month }}&camion={{ $selected_camion_id ?? '' }}&tonnage_filter={{ $tonnage_filter ?? '' }}"
   class="btn btn-success">
    <i class="fas fa-file-excel text-white"></i> Exporter en Excel
</a>
</div>

<div id="date_form" style="display: {{ $filter_type == 'date' ? 'block' : 'none' }};">
    <form method="GET" class="mb-3">
        <input type="hidden" name="filter_type" value="date">
        <input type="hidden" name="camion" value="{{ $selected_camion_id ?? '' }}">
        <input type="hidden" name="month" value="{{ $selected_month }}">
        <label for="date"><i class="fas fa-calendar-day text-info"></i> Sélectionner une date :</label>
        <input type="date" id="date" name="date" value="{{ $selected_date ?? '' }}" class="form-control d-inline-block w-auto">
        <button type="submit" class="btn btn-primary"><i class="fas fa-filter text-white"></i> Filtrer</button>
    </form>
</div>
<div id="month_form" style="display: {{ $filter_type == 'month' ? 'block' : 'none' }};">
    <form method="GET" class="mb-3">
        <input type="hidden" name="filter_type" value="month">
        <input type="hidden" name="camion" value="{{ $selected_camion_id ?? '' }}">
        <input type="hidden" name="date" value="{{ $selected_date }}">
        <label for="month"><i class="fas fa-calendar-alt text-warning"></i> Sélectionner un mois :</label>
        <input type="month" id="month" name="month" value="{{ $selected_month ?? '' }}" class="form-control d-inline-block w-auto">
        <button type="submit" class="btn btn-primary"><i class="fas fa-filter text-white"></i> Filtrer</button>
    </form>
</div>
<script>
    function showFilterForm() {
        const filterType = document.getElementById('filter_type').value;
        document.getElementById('date_form').style.display = filterType === 'date' ? 'block' : 'none';
        document.getElementById('month_form').style.display = filterType === 'month' ? 'block' : 'none';
    }
</script>

@if($show_camion_pagination)
    <div class="d-flex justify-content mt-4 mb-4 flex-wrap">
        @foreach($all_camions as $index => $cam)
            <a href="?filter_type=month&month={{ $selected_month }}&camion={{ $cam->id }}&page={{ $index + 1 }}"
               class="btn btn-sm {{ $current_camions->isNotEmpty() && $current_camions->first()->id == $cam->id ? 'btn-primary' : 'btn-outline-primary' }} mx-1 my-1">
                {{ $cam->numero }}
            </a>
        @endforeach
    </div>
@endif

<div class="table-responsive">
    <table class="table table-striped">
        <thead class="table-green">
            <tr>
                <th><i class="fas fa-calendar text-info"></i> Date</th>
                <th><i class="fas fa-truck text-primary"></i> Camion</th>
                <th><i class="fas fa-phone text-success"></i> Téléphone</th>
                <th><i class="fas fa-weight-hanging text-warning"></i> Tonnage</th>
                <th><i class="fas fa-dollar-sign text-danger"></i> P U</th>
                <th><i class="fas fa-box text-secondary"></i> Quantité</th>
                <th><i class="fas fa-money-bill-wave text-success"></i> Montant</th>
                <th><i class="fas fa-recycle text-info"></i> Chiffonage</th>
                <th><i class="fas fa-barcode text-primary"></i> N° BL</th>
                <th><i class="fas fa-info-circle text-warning"></i> Statut</th>
                <th class="text-nowrap"><i class="fas fa-cogs text-secondary"></i> Actions</th>
                <th>Vérification</th>
            </tr>
        </thead>
        <tbody>
            @foreach($current_camions as $camion)
                @foreach($date_range as $day)
                    @php
                        $dayKey = $day->format('Y-m-d');
                        $livraisonsDuJour = $livraisons_par_camion_et_date[$camion->id][$dayKey] ?? null;
                        $statutDuJour = $statuts_par_date[$camion->id][$dayKey] ?? null;
                    @endphp
                    @if($livraisonsDuJour)
                        @foreach($livraisonsDuJour as $loop2 => $livraison)
                            <tr id="camion-{{ $camion->id }}-date-{{ $dayKey }}-{{ $loop2 + 1 }}">
                                <td>{{ $day->format('j F Y') }}</td>
                                <td>{{ $camion->numero }}</td>
                                <td>{{ $camion->telephone }}</td>
                                <td>{{ $livraison->tonnage }}</td>
                                <td>{{ $livraison->prix_unitaire }}</td>
                                <td>{{ $livraison->quantite }}</td>
                                <td>{{ $livraison->montant }}</td>
                                <td>{{ $livraison->chiffonage }}</td>
                                <td>{{ $livraison->numero_bl }}</td>
                                <td>
                                    <span class="badge bg-success">
                                        <i class="bi bi-check-circle-fill"></i> {{ $livraison->statut_display }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="#" class="btn btn-warning btn-sm btn-edit-livraison" data-id="{{ $livraison->id }}">
                                            <i class="fas fa-edit text-white"></i>
                                        </a>
                                        <a href="{{ route('supprimer_livraison', $livraison->id, [], false) }}" class="btn btn-danger btn-sm"
                                           onclick="return confirm('Voulez-vous vraiment supprimer cette livraison ?');">
                                            <i class="fas fa-trash text-white"></i>
                                        </a>
                                        <a href="#" class="btn btn-success btn-sm" onclick="afficherIframe('{{ $dayKey }}', '{{ $camion->id }}')">
                                            <i class="fas fa-plus text-white"></i>
                                        </a>
                                    </div>
                                </td>
                                <td>
                                    @if($livraison->verification == 'verifié')
                                        <span class="text-primary">
                                            <i class="bi bi-patch-check-fill me-1"></i> Vérifié
                                        </span>
                                    @else
                                        <span class="text-warning">
                                            <i class="bi bi-x-circle-fill me-1"></i> Non vérifié
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <tr id="camion-{{ $camion->id }}-date-{{ $dayKey }}">
                            <td>{{ $day->format('j F Y') }}</td>
                            <td>{{ $camion->numero }}</td>
                            <td>{{ $camion->telephone }}</td>
                            <td colspan="6" class="text-center">Aucune livraison</td>
                            <td>
                                @if($statutDuJour == 'En attente' || $statutDuJour === null)
                                    <span class="badge bg-primary"><i class="bi bi-hourglass-split"></i> En attente</span>
                                @elseif($statutDuJour == 'En panne')
                                    <span class="badge bg-danger"><i class="bi bi-exclamation-triangle-fill"></i> En panne</span>
                                @elseif($statutDuJour == 'Travaille pas')
                                    <span class="badge bg-warning text-dark"><i class="bi bi-slash-circle"></i>  Travaille pas</span>
                                @else
                                    <span class="badge bg-warning text-dark">{{ $statutDuJour }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="#" class="btn btn-success btn-sm" onclick="afficherIframe('{{ $dayKey }}', '{{ $camion->id }}')">
                                        <i class="fas fa-plus text-white"></i>
                                    </a>
                                    @if($camion->id)
                                    <button class="btn btn-info btn-sm btn-edit-statut"
                                            data-camion-id="{{ $camion->id }}"
                                            data-date="{{ $dayKey }}">
                                        <i class="fas fa-edit text-white"></i>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endif
                @endforeach
            @endforeach
        </tbody>
    </table>
</div>

@if($show_camion_pagination)
    <div class="text-center mb-3">
        <h4>
            <span class="badge bg-primary">
                <i class="fas fa-truck"></i> Camion: {{ $current_camions->first()->numero ?? '' }}
            </span>
            <span class="badge bg-secondary ms-2">
                {{ $selected_date_obj ? $selected_date_obj->format('F Y') : '' }}
            </span>
        </h4>
    </div>
@endif
</div>

<div class="modal fade" id="modalEditLivraison" tabindex="-1" aria-labelledby="modalEditLivraisonLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditLivraisonLabel"><i class="fas fa-truck text-primary"></i>  Modifier Livraison</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalEditContent"></div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalEditStatut" tabindex="-1" aria-labelledby="modalEditStatutLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditStatutLabel">
                    <i class="fas fa-truck text-primary"></i> Modifier Statut Camion
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modalEditStatutContent"></div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $(".btn-edit-livraison").click(function(event) {
        event.preventDefault();
        var livraisonId = $(this).data("id");
        $.ajax({
            url: "/modifier/" + livraisonId + "/",
            type: "GET",
            headers: { "X-Requested-With": "XMLHttpRequest" },
            success: function(response) {
                $("#modalEditContent").html(response.html);
                $("#modalEditLivraison").modal("show");
            },
            error: function(xhr, status, error) {
                console.log("Erreur AJAX: ", xhr.responseText);
            }
        });
    });

    $(document).on("submit", "#editLivraisonForm", function(event) {
        event.preventDefault();
        var form = $(this);
        var url = form.attr("action");
        $.ajax({
            url: url,
            type: "POST",
            data: form.serialize(),
            headers: { "X-Requested-With": "XMLHttpRequest" },
            success: function(response) {
                if (response.success) {
                    $("#modalEditLivraison").modal("hide");
                    location.reload();
                } else {
                    $("#modalEditContent").html(response.html);
                }
            },
            error: function(xhr, status, error) {
                console.log("Erreur lors de la modification: ", xhr.responseText);
            }
        });
    });
});

$(document).on("click", ".btn-edit-statut", function() {
    var camionId = $(this).data("camion-id");
    var date = $(this).data("date");
    $.ajax({
        url: "/modifier-statut-camion/" + camionId + "/?date=" + date,
        type: "GET",
        headers: { "X-Requested-With": "XMLHttpRequest" },
        success: function(response) {
            $("#modalEditStatutContent").html(response);
            $("#modalEditStatut").modal("show");
        },
        error: function(xhr, status, error) {
            console.log("Erreur AJAX: ", xhr.responseText);
        }
    });
});

$(document).on("submit", "#editStatutForm", function(event) {
    event.preventDefault();
    var form = $(this);
    var url = form.attr("action");
    $.ajax({
        url: url,
        type: "POST",
        data: form.serialize(),
        headers: { "X-Requested-With": "XMLHttpRequest" },
        success: function(response) {
            if (response.success) {
                $("#modalEditStatut").modal("hide");
                location.reload();
            } else {
                $("#modalEditStatutContent").html(response);
            }
        },
        error: function(xhr, status, error) {
            console.log("Erreur lors de la modification du statut: ", xhr.responseText);
        }
    });
});
</script>

<script>
function afficherIframe(date, camionId) {
    $('iframe').remove();
    var iframe = document.createElement('iframe');
    iframe.src = "{{ route('ajouter_livraison', [], false) }}?date=" + date + "&camion=" + camionId;
    iframe.style.width = '100%';
    iframe.style.height = '300px';
    iframe.style.border = 'none';
    iframe.style.margin = '10px 0';
    iframe.id = 'iframe-' + camionId + '-' + date;
    var targetRow = $('tr[id^="camion-' + camionId + '-date-' + date + '"]').last();
    if (targetRow.length) {
        var newRow = $('<tr><td colspan="11"></td></tr>');
        newRow.find('td').append(iframe);
        targetRow.after(newRow);
    }
}

function actualiserListeLivraisons() {
    location.reload();
}

function fermerIframe() {
    $('iframe').parent().parent().remove();
}

window.addEventListener('message', function(event) {
    if (event.data.type === 'RELOAD_PAGE') {
        location.reload(true);
    }
});
</script>
@endsection
