@extends('layouts.base')

@section('content')
<div class="container-fluid mt-5">
    <h2><i class="fas fa-tachometer-alt text-primary"></i> Tableau de Bord 📊</h2><br>
<div class="mb-3">
    <label for="camion_filter"><i class="fas fa-truck text-primary"></i> Filtrer par camions :</label>
    <form method="GET" class="d-inline-block">
        <input type="hidden" name="filter_type" value="{{ $filter_type ?? 'None' }}">
        <input type="hidden" name="date" value="{{ $selected_date ?? 'None' }}">
        <input type="hidden" name="month" value="{{ $selected_month ?? 'None' }}">
        <input type="hidden" name="year" value="{{ $selected_year ?? 'None' }}">
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
    <label for="filter_type"><i class="fas fa-filter text-secondary"></i> Choisissez une Période :</label>
    <select id="filter_type" name="filter_type" class="form-control d-inline-block w-auto" onchange="showFilterForm()">
        <option value="">-- Sélectionnez --</option>
        <option value="date" {{ $filter_type == 'date' ? 'selected' : '' }}>Date Spécifique</option>
        <option value="month" {{ $filter_type == 'month' ? 'selected' : '' }}>Mois Spécifique</option>
        <option value="year" {{ $filter_type == 'year' ? 'selected' : '' }}>Année Spécifique</option>
    </select>
</div>

<div id="date_form" style="display: {{ $filter_type == 'date' ? 'block' : 'none' }};">
    <form method="GET" class="mb-3">
        <input type="hidden" name="filter_type" value="date">
        <input type="hidden" name="camion" value="{{ $selected_camion_id ?? '' }}">
        <input type="hidden" name="month" value="">
        <input type="hidden" name="year" value="">
        <label for="date"><i class="fas fa-calendar-day text-info"></i> Selectionnez Date :</label>
        <input type="date" id="date" name="date" value="{{ $selected_date ?? '' }}" class="form-control d-inline-block w-auto">
        <button type="submit" class="btn btn-primary ml-2"><i class="fas fa-filter"></i> Appliquer</button>
    </form>
</div>

<div id="month_form" style="display: {{ $filter_type == 'month' ? 'block' : 'none' }};">
    <form method="GET" class="mb-3">
        <input type="hidden" name="filter_type" value="month">
        <input type="hidden" name="camion" value="{{ $selected_camion_id ?? '' }}">
        <input type="hidden" name="date" value="">
        <input type="hidden" name="year" value="">
        <label for="month"><i class="fas fa-calendar-alt text-warning"></i> Selectionnez un Mois :</label>
        <input type="month" id="month" name="month" value="{{ $selected_month ?? '' }}" class="form-control d-inline-block w-auto">
        <button type="submit" class="btn btn-primary ml-2"><i class="fas fa-filter"></i> Appliquer</button>
    </form>
</div>

<div id="year_form" style="display: {{ $filter_type == 'year' ? 'block' : 'none' }};">
    <form method="GET" class="mb-3">
        <input type="hidden" name="filter_type" value="year">
        <input type="hidden" name="camion" value="{{ $selected_camion_id ?? '' }}">
        <input type="hidden" name="date" value="">
        <input type="hidden" name="month" value="">
        <label for="year"><i class="fas fa-calendar text-danger"></i> Selectionnez une Année :</label>
        <select id="year" name="year" class="form-control d-inline-block w-auto">
            @foreach($last_five_years as $year)
                <option value="{{ $year }}" {{ $selected_year == $year ? 'selected' : '' }}>{{ $year }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn btn-primary ml-2"><i class="fas fa-filter"></i> Appliquer</button>
    </form>
</div>

<a href="{{ route('dashboard', [], false) }}" class="btn btn-secondary mb-3">
    <i class="fas fa-undo"></i> Réinitialiser
</a>

<script>
function showFilterForm() {
    const filterType = document.getElementById('filter_type').value;
    document.getElementById('date_form').style.display = 'none';
    document.getElementById('month_form').style.display = 'none';
    document.getElementById('year_form').style.display = 'none';
    if (filterType === 'date') {
        document.getElementById('date_form').style.display = 'block';
    } else if (filterType === 'month') {
        document.getElementById('month_form').style.display = 'block';
    } else if (filterType === 'year') {
        document.getElementById('year_form').style.display = 'block';
    }
}
</script>
    <div class="row">
        <div class="col-md-4">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-weight text-light"></i> Total Tonnage</h5>
                    <p class="card-text">{{ $total_tonnage }} tonnes</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-money-bill-wave text-light"></i> Total Montant</h5>
                    <p class="card-text">{{ $total_montant }} F CFA</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-white bg-warning mb-3">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-truck text-light"></i> Nombre de Livraisons</h5>
                    <p class="card-text">{{ $total_livraisons }} Livraisons</p>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mt-4"><i class="fas fa-table text-secondary"></i> Tableau des Livraisons</h3>
    <table class="table table-striped">
        <thead>
            <tr>
                <th><i class="fas fa-calendar text-info"></i> Date</th>
                <th><i class="fas fa-truck text-primary"></i> N° Camion</th>
                <th><i class="fas fa-phone text-success"></i> Numéro Téléphone</th>
                <th><i class="fas fa-weight text-warning"></i> Tonnage</th>
                <th><i class="fas fa-dollar-sign text-danger"></i> Prix Unitaire</th>
                <th><i class="fas fa-box text-secondary"></i> Quantité</th>
                <th><i class="fas fa-money-bill-wave text-success"></i> Montant</th>
                <th><i class="fas fa-recycle text-info"></i> Chiffonage</th>
                <th><i class="fas fa-barcode text-primary"></i> N° BL</th>
                <th><i class="fas fa-info-circle text-warning"></i> Statut</th>
            </tr>
        </thead>
        <tbody>
            @forelse($livraisons_mois as $livraison)
                <tr>
                    <td>{{ django_date($livraison['date']) }}</td>
                    <td>{{ $livraison['camion_numero'] }}</td>
                    <td>{{ $livraison['camion_telephone'] }}</td>
                    <td>{{ $livraison['tonnage'] }}</td>
                    <td>{{ $livraison['prix_unitaire'] }}</td>
                    <td>{{ $livraison['quantite'] }}</td>
                    <td>{{ $livraison['montant'] }}</td>
                    <td>{{ $livraison['chiffonage'] }}</td>
                    <td>{{ $livraison['numero_bl'] }}</td>
                    <td><span class="badge bg-success"><i class="bi bi-check-circle-fill"></i> {{ $livraison['statut'] }}</span></td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center"><i class="fas fa-exclamation-circle text-danger"></i> Aucune livraison dans cette période</td>
                </tr>
            @endforelse
        </tbody>
    </table><br>

<h3 class="mt-4"><i class="fas fa-chart-line text-primary"></i> Évolution des Livraisons 📈</h3><br>
<div class="row">
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-primary text-white">
                <i class="fas fa-weight"></i> Évolution du Tonnage
            </div>
            <div class="card-body" style="height: 400px;">
                <canvas id="tonnageChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-success text-white">
                <i class="fas fa-money-bill-wave"></i> Évolution du Montant
            </div>
            <div class="card-body" style="height: 400px;">
                <canvas id="montantChart"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const moisLabels = JSON.parse('{!! $mois_labels !!}');
    const tonnageValues = JSON.parse('{!! $tonnage_values !!}');
    const montantValues = JSON.parse('{!! $montant_values !!}');

    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: 'top'
            }
        },
        scales: {
            x: {
                grid: {
                    display: false
                }
            },
            y: {
                beginAtZero: true
            }
        }
    };

    const tonnageCtx = document.getElementById('tonnageChart').getContext('2d');
    new Chart(tonnageCtx, {
        type: 'line',
        data: {
            labels: moisLabels,
            datasets: [{
                label: 'Tonnage (tonnes)',
                data: tonnageValues,
                borderColor: 'rgba(54, 162, 235, 1)',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                borderWidth: 2,
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            ...commonOptions,
            plugins: {
                ...commonOptions.plugins,
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.parsed.y.toFixed(2) + ' tonnes';
                        }
                    }
                }
            }
        }
    });

    const montantCtx = document.getElementById('montantChart').getContext('2d');
    new Chart(montantCtx, {
        type: 'bar',
        data: {
            labels: moisLabels,
            datasets: [{
                label: 'Montant (F CFA)',
                data: montantValues,
                backgroundColor: 'rgba(75, 192, 192, 0.7)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            ...commonOptions,
            plugins: {
                ...commonOptions.plugins,
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return new Intl.NumberFormat('fr-FR').format(context.parsed.y) + ' F CFA';
                        }
                    }
                }
            },
            scales: {
                ...commonOptions.scales,
                y: {
                    ticks: {
                        callback: function(value) {
                            return new Intl.NumberFormat('fr-FR').format(value);
                        }
                    }
                }
            }
        }
    });
});
</script>
</div>
@endsection
