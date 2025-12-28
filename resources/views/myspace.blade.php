@extends('layouts.app')

@section('title', 'Mon dashboard')

{{-- Section pour le fil d'Ariane --}}
@section('voidgrubs')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active" aria-current="page">Mon dashboard</li>
        </ol>
    </nav>
@endsection

{{-- Contenu principal du dashboard --}}
@section('content')
    <div class="container px-4 py-4">
        {{-- KPI Cards --}}
        <div class="mb-4 row d-flex justify-content-between g-2">
            <!-- Total équipements -->
            <div class="col-6">
                <div class="border-0 shadow-sm card h-100 position-relative">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <small class="text-uppercase text-muted">Total équipements</small>
                                <h3 class="mb-0 text-primary fw-bold">{{ $totalEquipements }}</h3>
                            </div>
                            <div class="ms-3 text-primary">
                                <i class="opacity-25 fas fa-layer-group fa-2x"></i>
                            </div>
                        </div>
                    </div>
                    <div class="top-0 position-absolute start-0 border-start border-5 border-primary rounded-start"></div>
                </div>
            </div>

            <!-- Équipements en panne -->
            <div class="col-6">
                <div class="border-0 shadow-sm card h-100 position-relative">
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="flex-grow-1">
                                <small class="text-uppercase text-muted">Équipements en panne</small>
                                <h3 class="mb-0 text-danger fw-bold">{{ $equipementsEnPanne }}</h3>
                            </div>
                            <div class="ms-3 text-danger">
                                <i class="opacity-25 fas fa-tools fa-2x"></i>
                            </div>
                        </div>
                    </div>
                    <div class="top-0 position-absolute start-0 border-start border-5 border-danger rounded-start"></div>
                </div>
            </div>
        </div>

        <div class="mb-4 card">
            <div class="card-header">
                <h5 class="mb-0">Vue d'ensemble des pannes rencontrées</h5>
            </div>
            <div class="card-body">
                @if (count($diagnosticsCounts ?? []) > 0)
                    <ul class="list-group list-group-flush">
                        @foreach ($diagnosticsCounts as $diagnostic => $count)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                {{ $diagnostic }}
                                <span class="badge bg-primary rounded-pill">{{ $count }}</span>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p>Aucun diagnostic de panne enregistré pour vos équipements pour le moment.</p>
                @endif
            </div>
        </div>




        <!-- Statistiques détaillées (Listes) -->
        <div class="row g-4">
            @php
                $sections = [
                    ['title' => 'Répartition par catégorie', 'data' => $equipementsParCategorie, 'badge' => 'primary'],
                    ['title' => 'Statut des pannes des équipements', 'data' => $pannesParStatut, 'badge' => 'warning'],
                ];
            @endphp

            @foreach ($sections as $section)
                <div class="col-6"> {{-- Adapté pour 4 colonnes sur grand écran --}}
                    <div class="border-0 shadow-sm card h-100">
                        <div class="py-3 bg-white card-header border-bottom-0">
                            <h5 class="mb-0 fw-semibold text-secondary">{{ $section['title'] }}</h5>
                        </div>
                        <div class="pt-0 card-body">
                            <ul class="list-group list-group-flush">
                                @forelse ($section['data'] as $label => $value)
                                    <li
                                        class="py-2 border-0 list-group-item d-flex justify-content-between align-items-center">
                                        <span>{{ $label }}</span>
                                        <span class="badge bg-{{ $section['badge'] }} rounded-pill px-3 py-2">
                                            {{ $value }}
                                        </span>
                                    </li>
                                @empty
                                    <li class="border-0 list-group-item text-muted fst-italic">Aucune donnée disponible</li>
                                @endforelse
                            </ul>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Conteneurs des graphiques --}}
        <div class="mt-5 row">
            <div class="col-md-6">
                <div class="shadow-sm card">
                    <div class="bg-white border-0 card-header">
                        <h5 class="mb-0 text-secondary">Équipements par catégorie (Évolution)</h5>
                    </div>
                    <div class="card-body">
                        <div id="categorieLineChart"></div>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="shadow-sm card">
                    <div class="bg-white border-0 card-header">
                        <h5 class="mb-0 text-secondary">Statut de pannnes (Évolution)</h5>
                    </div>
                    <div class="card-body">
                        <div id="panneLineChart"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Script JavaScript pour ApexCharts --}}


        {{-- Styles CSS --}}
        <style>
            .card {
                border-radius: 0.75rem;
                transition: all 0.2s ease-in-out;
            }

            .card:hover {
                transform: translateY(-2px);
                box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.05) !important;
            }

            .badge {
                font-size: 0.85rem;
            }

            .card-header h5 {
                font-size: 1.1rem;
            }
        </style>
    @endsection


    @section('scripts')

        <script>
            document.addEventListener("DOMContentLoaded", () => {
                // Conversion des données PHP en format JS
                // ATTENTION : categoriesData et pannesData sont maintenant des TABLEAUX DE SÉRIES !
                const categoriesLabels = @json($categoriesLabels); // Ex: ["2023-01", "2023-02"]
                const categoriesData =
                    @json($categoriesData); // Ex: [{name: "PC", data: [10, 12]}, {name: "Imprimante", data: [5, 6]}]
                const pannesLabels = @json($pannesLabels); // Ex: ["2023-01", "2023-02"]
                const pannesData =
                    @json($pannesData); // Ex: [{name: "En attente", data: [2, 1]}, {name: "Résolue", data: [3, 4]}]

                // POUR DÉBOGUER : Affichez les données dans la console de votre navigateur (F12)
                console.log("Categories Labels:", categoriesLabels);
                console.log("Categories Data:", categoriesData);
                console.log("Pannes Labels:", pannesLabels);
                console.log("Pannes Data:", pannesData);

                // Chart 1: Équipements par catégorie (Évolution)
                if (document.querySelector("#categorieLineChart") && categoriesData.length > 0) {
                    new ApexCharts(document.querySelector("#categorieLineChart"), {
                        series: categoriesData, // C'est déjà un tableau de séries d'après le contrôleur
                        chart: {
                            type: 'line',
                            height: 350,
                            zoom: {
                                enabled: false
                            }
                        },
                        stroke: {
                            curve: 'straight',
                            width: 3
                        },
                        markers: {
                            size: 5
                        },
                        xaxis: {
                            categories: categoriesLabels, // Les labels des mois/périodes
                            title: {
                                text: 'Période'
                            }
                        },
                        yaxis: {
                            min: 0,
                            title: {
                                text: "Nombre d'équipements"
                            },
                            labels: {
                                formatter: function(value) {
                                    return parseInt(value); // Assure que les labels Y sont des entiers
                                }
                            }
                        },
                        colors: ['#0d6efd', '#28a745', '#ffc107', '#6f42c1',
                            '#fd7e14'
                        ], // Plus de couleurs pour plusieurs séries
                        tooltip: {
                            enabled: true
                        }
                    }).render();
                } else {
                    console.warn("Element #categorieLineChart not found or categoriesData is empty.");
                }


                // Chart 2: Pannes par statut (Évolution)
                if (document.querySelector("#panneLineChart") && pannesData.length > 0) {
                    new ApexCharts(document.querySelector("#panneLineChart"), {
                        series: pannesData, // C'est déjà un tableau de séries d'après le contrôleur
                        chart: {
                            type: 'line',
                            height: 350,
                            zoom: {
                                enabled: false
                            }
                        },
                        stroke: {
                            curve: 'straight',
                            width: 3
                        },
                        markers: {
                            size: 5
                        },
                        xaxis: {
                            categories: pannesLabels, // Les labels des mois/périodes
                            title: {
                                text: 'Période'
                            }
                        },
                        yaxis: {
                            min: 0,
                            title: {
                                text: 'Nombre de pannes'
                            },
                            labels: {
                                formatter: function(value) {
                                    return parseInt(value); // Assure que les labels Y sont des entiers
                                }
                            }
                        },
                        colors: ['#dc3545', '#17a2b8', '#20c997',
                            '#6c757d'
                        ], // Couleurs pour les statuts de pannes
                        tooltip: {
                            enabled: true
                        }
                    }).render();
                } else {
                    console.warn("Element #panneLineChart not found or pannesData is empty.");
                }
            });
        </script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var diagnosticsData = @json($diagnosticsCounts);
                var labels = Object.keys(diagnosticsData);
                var data = Object.values(diagnosticsData);

                var ctx = document.getElementById('diagnosticsChart').getContext('2d');
                new Chart(ctx, {
                    type: 'line', // or 'pie' or 'doughnut'
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Nombre de diagnostics',
                            data: data,
                            backgroundColor: [
                                'rgba(255, 99, 132, 0.6)',
                                'rgba(54, 162, 235, 0.6)',
                                'rgba(255, 206, 86, 0.6)',
                                'rgba(75, 192, 192, 0.6)',
                                'rgba(153, 102, 255, 0.6)',
                                'rgba(255, 159, 64, 0.6)',
                                'rgba(199, 199, 199, 0.6)'
                            ],
                            borderColor: [
                                'rgba(255, 99, 132, 1)',
                                'rgba(54, 162, 235, 1)',
                                'rgba(255, 206, 86, 1)',
                                'rgba(75, 192, 192, 1)',
                                'rgba(153, 102, 255, 1)',
                                'rgba(255, 159, 64, 1)',
                                'rgba(199, 199, 199, 1)'
                            ],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Nombre'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Diagnostic de Panne'
                                }
                            }
                        },
                        plugins: {
                            legend: {
                                display: false // Hide legend for bar/pie if labels are self-explanatory
                            },
                            title: {
                                display: true,
                                text: 'Fréquence des diagnostics de pannes'
                            }
                        }
                    }
                });
            });
        </script>



    @endsection
