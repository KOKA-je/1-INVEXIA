@extends('layouts.app')
@section('title', 'Mes equipements')
@section('voidgrubs')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active" aria-current="page">Mes equipements</li>
        </ol>
    </nav>
@endsection

@section('content')
    <div class="container py-4">
        @if ($equipements->isEmpty())
            <div class="py-5 text-center card">
                <div class="card-body">
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="#ccc" class="mb-3"
                        viewBox="0 0 24 24">
                        <path
                            d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z" />
                    </svg>
                    <p class="text-muted">Il n'y a rien pour le moment.</p>
                </div>
            </div>
        @else
            <div id="equipementsCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="6000">
                <div class="carousel-inner">
                    @foreach ($equipements->chunk(3) as $chunk)
                        <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                            <div class="row g-4">
                                @foreach ($chunk as $eq)
                                    @php
                                        $categorie = strtolower($eq->categorieEquipement->lib_cat ?? '');
                                        $icons = [
                                            'uc' => 'bi-pc',
                                            'portable' => 'bi-laptop',
                                            'clavier' => 'bi-keyboard',
                                            'imprimante' => 'bi-printer',
                                            'telephone ip' => 'bi-telephone',
                                            'scanner' => 'bi-scanner',
                                            'ecran' => 'bi-display',
                                            'onduleur' => 'bi-battery-charging',
                                            'routeur' => 'bi-router',
                                            'switch' => 'bi-hdd-network',
                                            'serveur' => 'bi-server',
                                        ];
                                        $iconClass = $icons[$categorie] ?? 'bi-box';

                                        // Get the latest panne, which could be null
                                        $latestPanne = $eq->pannes->sortByDesc('created_at')->first();

                                        // Determine if a new panne can be reported for this equipment
                                        $canReportNewPanne = true;

                                        if ($latestPanne) {
                                            if (
                                                $latestPanne->sta_pan === 'En attente' ||
                                                $latestPanne->sta_pan === 'En cours'
                                            ) {
                                                $canReportNewPanne = false;
                                            }
                                        }
                                    @endphp
                                    <div class="col-md-4">
                                        <div
                                            class="overflow-hidden transition-all border-0 shadow-sm card h-100 hover-shadow position-relative">
                                            <div class="card-body">
                                                <div class="mb-3 text-center">
                                                    <div class="icon-wrapper bg-light-primary rounded-circle d-inline-flex align-items-center justify-content-center"
                                                        style="width: 80px; height: 80px;">
                                                        <i class="bi {{ $iconClass }} tw-text-orange-600"
                                                            style="font-size: 2.5rem;"></i>
                                                    </div>
                                                </div>

                                                <h5 class="mb-3 text-center card-title">
                                                    {{ $eq->designation_eq ?? 'Sans désignation' }}
                                                    @if ($eq->num_inventaire_eq)
                                                        <small
                                                            class="mt-1 d-block text-muted">{{ $eq->num_inventaire_eq }}</small>
                                                    @endif
                                                </h5>

                                                <div class="gap-2 mt-4 d-flex flex-column">
                                                    <a href="{{ route('equipements.show', $eq->id) }}"
                                                        class=" btn btn-outline-success btn-sm rounded-pill">
                                                        <i class="bi bi-eye me-1"></i> Détails
                                                    </a>

                                                    @if ($canReportNewPanne)
                                                        <button type="button"
                                                            class="text-white btn btn-warning btn-sm rounded-pill"
                                                            data-bs-toggle="modal" data-bs-target="#reportPanneModal"
                                                            data-equipement-id="{{ $eq->id }}">
                                                            <i class="bi bi-exclamation-triangle me-1"></i> Signaler une
                                                            panne
                                                        </button>
                                                    @else
                                                        @if ($latestPanne->sta_pan === 'En cours')
                                                            <button class="btn btn-outline-success btn-sm rounded-pill"
                                                                disabled>
                                                                <i class="bi bi-tools me-1"></i> Panne en cours traitement
                                                            </button>
                                                        @else
                                                            <button class="btn btn-outline-warning btn-sm rounded-pill"
                                                                disabled>
                                                                <i class="bi bi-tools me-1"></i>Panne en attente de
                                                                diagnostique
                                                            </button>
                                                        @endif
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

                {{-- Carousel controls --}}
                @if ($equipements->count() > 3)
                    <div class="mt-4 d-flex justify-content-center">
                        <button class="mx-1 btn btn-sm btn-light rounded-circle" data-bs-target="#equipementsCarousel"
                            data-bs-slide="prev" style="width: 40px; height: 40px;">
                            <i class="bi bi-chevron-left"></i>
                        </button>



                        <button class="mx-1 btn btn-sm btn-light rounded-circle" data-bs-target="#equipementsCarousel"
                            data-bs-slide="next" style="width: 40px; height: 40px;">
                            <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                @endif
            </div>
        @endif
    </div>

    <style>
        .hover-shadow {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .hover-shadow:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
        }

        .icon-wrapper {
            transition: transform 0.3s ease;
        }

        .card:hover .icon-wrapper {
            transform: scale(1.1);
        }

        .card {
            position: relative;
        }
    </style>

    <!-- Report Problem Modal -->
    @include('pages.pannes.modals.report')





@endsection
