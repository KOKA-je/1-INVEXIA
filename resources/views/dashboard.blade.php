@extends('layouts.app')

@section('title', 'Dashboard')

@section('voidgrubs')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
        </ol>
    </nav>
@endsection


@section('content')

    <div class="px-4 container-fluid">
        <div class="mb-4 row">
            <div class="mb-4 col-xl-3 col-md-6">
                <div class="py-2 shadow card border-left-primary h-100">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="mr-2 col">
                                <div class="mb-1 text-xs font-weight-bold text-secondary text-uppercase">
                                    Équipements Totaux</div>
                                <div class="mb-0 text-gray-800 h5 font-weight-bold">{{ $globalStats['total'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="text-gray-300 fas fa-server fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-4 col-xl-3 col-md-6">
                <div class="py-2 shadow card border-left-success h-100">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="mr-2 col">
                                <div class="mb-1 text-xs font-weight-bold text-success text-uppercase">
                                    Disponibles</div>
                                <div class="mb-0 text-gray-800 h5 font-weight-bold">{{ $globalStats['disponibles'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="text-gray-300 fas fa-check-circle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-4 col-xl-3 col-md-6">
                <div class="py-2 shadow card border-left-warning h-100">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="mr-2 col">
                                <div class="mb-1 text-xs font-weight-bold text-warning text-uppercase">
                                    En panne</div>
                                <div class="mb-0 text-gray-800 h5 font-weight-bold">{{ $globalStats['en_panne'] }}</div>
                                <div class="mt-1 text-xs text-muted">{{ $pourcentagePanne }}% du parc</div>
                            </div>
                            <div class="col-auto">
                                <i class="text-gray-300 fas fa-exclamation-triangle fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-4 col-xl-3 col-md-6">
                <div class="py-2 shadow card border-left-danger h-100">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="mr-2 col">
                                <div class="mb-1 text-xs font-weight-bold text-danger text-uppercase">
                                    Pannes en attente</div>
                                <div class="mb-0 text-gray-800 h5 font-weight-bold">{{ $panneStats['en_attente'] }}</div>
                                @if ($panneStats['pannes_critiques'] > 0)
                                    <div class="mt-1 text-xs text-danger">{{ $panneStats['pannes_critiques'] }} critiques
                                    </div>
                                @endif
                            </div>
                            <div class="col-auto">
                                <i class="text-gray-300 fas fa-clock fa-2x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-5 shadow card">
            <div class="py-3 card-header d-flex justify-content-between">
                <h5 class="m-0 font-weight-bold text-secondary">Statut des Équipements par Catégorie</h5>
                <a href="{{ route('reports.equipments') }}" target="_blank" class="btn btn-outline-danger me-2">
                    <i class="fas fa-file-pdf me-1"></i> rapport Équipements
                </a>
            </div>
            <div class="card-body">
                @if ($equipementsStats->isEmpty())
                    <div class="text-center">
                        <p class="text-muted">Aucun équipement enregistré</p>
                    </div>
                @else
                    <div id="equipementsCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="8000">
                        <div class="carousel-inner">
                            @foreach ($equipementsStats->chunk(3) as $chunk)
                                <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                                    <div class="row g-4">
                                        @foreach ($chunk as $type)
                                            <div class="col-md-4">
                                                <div class="mx-2 border-0 shadow-sm card h-100">
                                                    <div class="p-3 text-center card-body">
                                                        @php
                                                            $libelle = strtolower($type->lib_cat);
                                                            // Icône dynamique
                                                            $icon = match ($libelle) {
                                                                'portable' => 'fas fa-laptop',
                                                                'imprimante' => 'fas fa-print',
                                                                'pocket wi-fi', 'box wi-fi', 'routeur' => 'fas fa-wifi',
                                                                'scanner' => 'fas fa-barcode',
                                                                'souris' => 'fas fa-mouse',
                                                                'clavier' => 'fas fa-keyboard',
                                                                'telephone ip' => 'fas fa-phone',
                                                                'telephone portable' => 'fas fa-mobile-alt',
                                                                'uc' => 'fas fa-desktop',
                                                                'tablette famoco' => 'fas fa-tablet-alt',
                                                                'ecran' => 'fas fa-tv',
                                                                'onduleur' => 'fas fa-battery-full',
                                                                'switch' => 'fas fa-network-wired',
                                                                'serveur' => 'fas fa-server',
                                                                default => 'fas fa-cogs',
                                                            };
                                                        @endphp

                                                        <div class="mb-3 tw-text-orange-600">
                                                            <i class="{{ $icon }} fa-3x"></i>
                                                        </div>

                                                        <h5 class="mb-3 card-title">{{ $type->lib_cat }}</h5>

                                                        <div class="mb-3 progress" style="height: 12px;">
                                                            <div class="progress-bar bg-success"
                                                                style="width: {{ $type->total > 0 ? ($type->disponibles / $type->total) * 100 : 0 }}%"
                                                                title="Disponibles: {{ $type->disponibles }} ({{ $type->total > 0 ? round(($type->disponibles / $type->total) * 100) : 0 }}%)">
                                                            </div>
                                                            <div class="progress-bar bg-warning"
                                                                style="width: {{ $type->total > 0 ? ($type->attribues / $type->total) * 100 : 0 }}%"
                                                                title="Attribués: {{ $type->attribues }} ({{ $type->total > 0 ? round(($type->attribues / $type->total) * 100) : 0 }}%)">
                                                            </div>
                                                            <div class="progress-bar bg-danger"
                                                                style="width: {{ $type->total > 0 ? ($type->en_panne / $type->total) * 100 : 0 }}%"
                                                                title="En panne: {{ $type->en_panne }} ({{ $type->total > 0 ? round(($type->en_panne / $type->total) * 100) : 0 }}%)">
                                                            </div>
                                                        </div>

                                                        <div class="flex-wrap d-flex justify-content-around">
                                                            <div class="px-2 mb-2 text-center">
                                                                <div class="mb-0 h5">{{ $type->total }}</div>
                                                                <small class="text-muted">Total</small>
                                                            </div>
                                                            <div class="px-2 mb-2 text-center">
                                                                <div class="mb-0 h5 text-success">{{ $type->disponibles }}
                                                                </div>
                                                                <small class="text-muted">
                                                                    <i class="fas fa-check-circle text-success"></i> Disp.
                                                                </small>
                                                            </div>
                                                            <div class="px-2 mb-2 text-center">
                                                                <div class="mb-0 h5 text-warning">{{ $type->attribues }}
                                                                </div>
                                                                <small class="text-muted">
                                                                    <i class="fas fa-user-tag text-warning"></i> Attrib.
                                                                </small>
                                                            </div>
                                                            @if ($type->en_panne > 0)
                                                                <div class="px-2 mb-2 text-center">
                                                                    <div class="mb-0 h5 text-danger">{{ $type->en_panne }}
                                                                    </div>
                                                                    <small class="text-muted">
                                                                        <i
                                                                            class="fas fa-exclamation-triangle text-danger"></i>
                                                                        Panne
                                                                    </small>
                                                                </div>
                                                            @endif
                                                            @if ($type->reformes > 0)
                                                                <div class="px-2 mb-2 text-center">
                                                                    <div class="mb-0 h5 text-secondary">
                                                                        {{ $type->reformes }}</div>
                                                                    <small class="text-muted">
                                                                        <i class="fas fa-recycle text-secondary"></i> Réf.
                                                                    </small>
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4 text-center">
                            <button class="mx-1 btn btn-sm btn-outline-primary rounded-circle"
                                data-bs-target="#equipementsCarousel" data-bs-slide="prev">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button class="mx-1 btn btn-sm btn-outline-primary rounded-circle"
                                data-bs-target="#equipementsCarousel" data-bs-slide="next">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <div class="row">
            <div class="mb-4 col-lg-6">
                <div class="shadow card h-100">
                    <div class="py-3 card-header d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-secondary">Statut des Pannes</h6>
                        <span class="badge bg-info">Temps moyen: {{ round($panneStats['temps_moyen']) }}h</span>
                    </div>
                    <div class="card-body">
                        <canvas id="statusChart" height="200"></canvas>
                    </div>
                </div>
            </div>

            <div class="mb-4 col-lg-6">
                <div class="shadow card h-100">
                    <div class="py-3 card-header">
                        <h6 class="m-0 font-weight-bold text-secondary">Pannes par Catégorie (Top 5)</h6>
                    </div>
                    <div class="card-body">
                        <canvas id="categoryChart" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="mb-4 col-lg-12">
                <div class="shadow card h-100">
                    <div class="py-3 card-header d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-secondary">Dernières Pannes</h6>
                        <div class="gap-1 d-flex">
                            <a href="{{ route('pannes.index') }}" class="btn btn-sm btn-outline-warning">Voir
                                toutes</a>
                            {{-- <a href="{{ route('reports.pannes') }}" target="_blank"
                                class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-file-pdf me-1"></i> rapport Pannes
                            </a> --}}
                        </div>
                    </div>


                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Équipement</th>
                                        <th>Catégorie</th>
                                        <th>Description</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentPannes as $panne)
                                        <tr>
                                            <td>{{ $panne->date_signa->format('d/m/Y H:i') }}</td>
                                            <td>{{ $panne->equipement->nom_eq ?? 'N/A' }}</td>
                                            <td>{{ $panne->equipement->categorieEquipement->lib_cat ?? 'N/A' }}</td>
                                            <td>{{ Str::limit($panne->lib_pan, 40) }}</td>
                                            <td>
                                                @if ($panne->sta_pan == 'En attente')
                                                    <span class="badge bg-warning">En attente</span>
                                                @elseif($panne->sta_pan == 'En cours')
                                                    <span class="badge bg-danger">En cours</span>
                                                @else
                                                    <span class="badge bg-success">Résolue</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">Aucune panne récente</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>


            {{-- <div class="mb-4 col-lg-4">
                <div class="shadow card h-100">
                    <div class="py-3 text-white card-header bg-danger">
                        <h6 class="m-0 font-weight-bold">Équipements en attente d'interventions</h6>
                    </div>
                    <div class="card-body">
                        @if ($equipementsUrgents->isEmpty())
                            <p class="text-center text-muted">Aucun équipement nécessitant une intervention urgente</p>
                        @else
                            <div class="list-group">
                                @foreach ($equipementsUrgents as $equipement)
                                    <div class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">{{ $equipement->nom_eq }}</h6>
                                            <span class="badge bg-danger">{{ $equipement->duree_panne }}h</span>
                                        </div>
                                        <p class="mb-1">{{ $equipement->categorieEquipement->lib_cat ?? 'N/A' }}</p>
                                        <small>Dernière panne:
                                            {{ $equipement->pannes->first()->date_signa->format('d/m/Y H:i') }}</small>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div> --}}
        </div>



        <div class="row">
            <div class="col-lg-12">
                <div class="mb-4 shadow card">
                    <div class="py-3 card-header">
                        <h6 class="m-0 font-weight-bold text-secondary">Statistiques Globales des Pannes</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-4 row justify-content-center">
                            <div class="col-auto text-center">
                                <div class="p-3 text-white bg-primary rounded-circle d-inline-flex align-items-center justify-content-center"
                                    style="width: 100px; height: 100px;">
                                    <i class="fas fa-wrench fa-3x"></i>
                                </div>
                                <h3 class="mt-3 mb-0 display-4 text-secondary font-weight-bold">{{ $total_pannes }}</h3>
                                <p class="text-muted">Pannes enregistrées au total</p>
                            </div>
                        </div>
                        <div class="mt-4 row">
                            <div class="col-md-6">
                                <h5 class="mb-3 text-secondary">Top 5 des Pannes Fréquentes</h5>
                                @if ($top_diagnostics->isEmpty())
                                    <p class="text-center text-muted">Aucun diagnostic enregistré pour l'instant.</p>
                                @else
                                    <ul class="list-group list-group-flush">
                                        @foreach ($top_diagnostics as $diagnostic)
                                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                                <span><i class="fas fa-chevron-right text-muted me-2"></i>
                                                    {{ $diagnostic->diagnostic }}</span>
                                                <span class="badge bg-info rounded-pill">{{ $diagnostic->count }}
                                                    ({{ $diagnostic->percentage }}%)
                                                </span>
                                            </li>
                                        @endforeach
                                    </ul>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <h5 class="mb-3 text-secondary">Répartition Complète des pannes</h5>
                                @if ($diagnostics_repartition->isEmpty())
                                    <p class="text-center text-muted">Aucune répartition de diagnostic disponible.</p>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-striped table-hover table-sm">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th scope="col">Panne</th>
                                                    <th scope="col">Nombre</th>
                                                    <th scope="col">Pourcentage</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($diagnostics_repartition as $item)
                                                    <tr>
                                                        <td>{{ $item->diagnostic }}</td>
                                                        <td><span class="badge bg-secondary">{{ $item->count }}</span>
                                                        </td>
                                                        <td><span class="badge bg-primary">{{ $item->percentage }}%</span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div> {{-- End px-4 container-fluid --}}


    @section('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Graphique Statut des Pannes
                const statusCtx = document.getElementById('statusChart').getContext('2d');
                new Chart(statusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['En attente', 'En cours', 'Résolues'],
                        datasets: [{
                            data: [
                                {{ $panneStats['en_attente'] }},
                                {{ $panneStats['en_cours'] }},
                                {{ $panneStats['resolues'] }}
                            ],
                            backgroundColor: [
                                'rgba(255, 193, 7, 0.8)', // Warning
                                'rgba(220, 53, 69, 0.8)', // Danger
                                'rgba(40, 167, 69, 0.8)' // Success
                            ],
                            borderColor: [
                                'rgba(255, 193, 7, 1)',
                                'rgba(220, 53, 69, 1)',
                                'rgba(40, 167, 69, 1)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'bottom'
                            }
                        }
                    }
                });

                // Graphique Pannes par Catégorie
                const categoryCtx = document.getElementById('categoryChart').getContext('2d');
                const categoryData = {!! json_encode($maintenanceTrends['categories']) !!};

                new Chart(categoryCtx, {
                    type: 'bar',
                    data: {
                        labels: categoryData.map(item => item.lib_cat),
                        datasets: [{
                            label: 'Nombre de Pannes',
                            data: categoryData.map(item => item.pannes_count),
                            backgroundColor: 'rgba(78, 115, 223, 0.8)',
                            borderColor: 'rgba(78, 115, 223, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        },
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            });
        </script>



    @endsection
@endsection
