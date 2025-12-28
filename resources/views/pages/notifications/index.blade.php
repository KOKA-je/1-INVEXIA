@extends('layouts.app')

@section('title', 'Notifications')

@section('voidgrubs')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active" aria-current="page">Notifications</li>
        </ol>
    </nav>
@endsection

@section('content')
    <div class="container">



        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($notifications->count() > 0)
            <div class="mb-3 d-flex justify-content-end">
                <form method="POST" action="{{ route('notifications.readAll') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-envelope-open me-1"></i> Tout marquer comme lu
                    </button>
                </form>
            </div>

            @foreach ($notifications as $notification)
                <div class="card mb-3 {{ $notification->read_at ? '' : 'border-primary shadow-sm' }}">
                    <div class="card-body">
                        <div class="mb-2 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 card-title">
                                @php
                                    $notificationType = $notification->data['type'] ?? 'general';
                                @endphp
                                @switch($notificationType)
                                    @case('nouvelle attribution')
                                        <span class="badge bg-success me-2"><i class="bi bi-box-arrow-in-right me-1"></i> Nouvelle
                                            Attribution</span>
                                    @break

                                    @case('ajout machines')
                                        <span class="badge bg-info me-2"><i class="bi bi-plus-square me-1"></i> Machines
                                            Ajoutées</span>
                                    @break

                                    @case('mise à jour attribution')
                                        <span class="badge bg-danger me-2"><i class="bi bi-box-arrow-left me-1"></i> Attribution
                                            mise à jour</span>
                                    @break

                                    @case('retrait machines')
                                        <span class="text-white badge bg-warning me-2"><i class="bi bi-dash-square me-1"></i>
                                            Machines Retirées</span>
                                    @break

                                    @case('retrait attribution')
                                        <span class="text-white badge bg-danger me-2"><i class="bi bi-dash-square me-1"></i>
                                            Attribution Retirée</span>
                                    @break

                                    @case('panne')
                                        <span class="text-white badge bg-danger me-2"><i class="bi bi-wrench me-1"></i> Signalement
                                            de
                                            Panne</span>
                                    @break
                                @endswitch
                                {{ $notification->data['message'] ?? 'Vous avez une nouvelle notification.' }}
                            </h5>
                            @if (!$notification->read_at)
                                <form action="{{ route('notifications.read', $notification->id) }}" method="POST"
                                    class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success">
                                        <i class="bi bi-check-circle me-1"></i> Marquer comme lu
                                    </button>
                                </form>
                            @else
                                <span class="badge bg-secondary"><i class="bi bi-check-all me-1"></i> Lue</span>
                            @endif
                        </div>

                        {{-- Section for Equipment Details (for attribution-related notifications) --}}
                        @if (!empty($notification->data['equipments']))
                            <h6 class="pt-2 mt-2 mb-2 card-subtitle text-muted border-top">
                                <i class="bi bi-pc-display-horizontal me-1"></i> Équipements concernés :
                            </h6>
                            <ul class="mb-2 list-group list-group-flush">
                                @foreach ($notification->data['equipments'] as $equipment)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ $equipment['num_inventaire'] ?? 'Nom inconnu' }}</strong>
                                            @if (isset($equipment['num_serie_eq']) || isset($equipment['num_inventaire_eq']))
                                                <br><small class="text-muted">
                                                    @if (isset($equipment['num_serie_eq']))
                                                        N° Série: {{ $equipment['num_serie_eq'] }}
                                                    @endif
                                                    @if (isset($equipment['num_serie_eq']) && isset($equipment['num_inventaire_eq']))
                                                        &bull;
                                                    @endif
                                                    @if (isset($equipment['num_inventaire_eq']))
                                                        N° Inventaire: {{ $equipment['num_inventaire_eq'] }}
                                                    @endif
                                                </small>
                                            @endif
                                        </div>

                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        {{-- Section for Panne Details --}}
                        @if (($notification->data['type'] ?? '') === 'panne')
                            <h6 class="pt-2 mt-2 mb-2 card-subtitle text-muted border-top">
                                <i class="bi bi-info-circle me-1"></i> Détails de la Panne :
                            </h6>
                            <p class="mb-1 card-text">
                                @php
                                    $statut = strtolower($notification->data['statut'] ?? '');
                                    $badgeClass = '';
                                    switch ($statut) {
                                        case 'en cours':
                                            $badgeClass = 'bg-info';
                                            break;
                                        case 'en attente':
                                            $badgeClass = 'bg-warning text-dark';
                                            break;
                                        case 'résolue':
                                            $badgeClass = 'bg-success';
                                            break;
                                        case 'annulée':
                                            $badgeClass = 'bg-danger';
                                            break;
                                    }
                                @endphp
                                <strong>Statut: <span
                                        class="badge {{ $badgeClass }}">{{ ucfirst($statut) }}</span></strong>
                            </p>
                            <p class="mb-1 card-text">
                                <small>
                                    <i class="bi bi-file-text me-1"></i> Description de l'utilisateur :
                                    {{ $notification->data['libelle'] ?? 'Non précisé' }}<br>
                                    <i class="bi bi-chat-dots me-1"></i> Diagnostic :
                                    {{ $notification->data['diagnostic'] ?? '-' }}<br>

                                </small>
                            </p>
                        @endif

                        {{-- Information about who performed the action --}}
                        @if (isset($notification->data['attributed_by']))
                            <p class="pt-2 mt-3 card-text border-top">
                                <small class="text-muted">
                                    <i class="bi bi-person-fill me-1"></i> Action effectuée par :
                                    <strong>{{ $notification->data['attributed_by'] }}</strong>
                                </small>
                            </p>
                        @elseif (isset($notification->data['updated_by']))
                            <p class="pt-2 mt-3 card-text border-top">
                                <small class="text-muted">
                                    <i class="bi bi-person-fill me-1"></i> Mise à jour par :
                                    <strong>{{ $notification->data['updated_by'] }}</strong>
                                </small>
                            </p>
                        @endif


                        <p class="mt-3 card-text">
                            <small class="text-muted">
                                <i class="bi bi-clock me-1"></i> {{ $notification->created_at->diffForHumans() }}
                                @if ($notification->read_at)
                                    <span class="ms-2"><i class="bi bi-eye-fill me-1"></i> Lu le
                                        {{ $notification->read_at->format('d/m/Y H:i') }}</span>
                                @endif
                            </small>
                        </p>
                    </div>
                </div>
            @endforeach

            <div class="mt-4 d-flex justify-content-center">
                {{ $notifications->links() }}
            </div>
        @else
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
        @endif
    </div>
@endsection

{{-- Add this section at the bottom of your Blade file for Bootstrap Icons --}}
@push('scripts')
    {{-- <link rel="stylesheet" href="{{ asset('path/to/bootstrap-icons/font/bootstrap-icons.css') }}"> --}}

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
@endpush
