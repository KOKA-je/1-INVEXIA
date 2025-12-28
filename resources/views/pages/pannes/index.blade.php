@extends('layouts.app')
@section('title', 'Gestion des Pannes')

{{-- Section pour le fil d'Ariane --}}
@section('voidgrubs')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active" aria-current="page">Pannes</li>
        </ol>
    </nav>
@endsection

@section('content')
    <div class="container">
        {{-- Formulaire de recherche et de filtrage pour les Pannes --}}
        <form method="GET" class="mb-4 row g-4 align-items-end">
            <div class="col-md-2">
                <label for="search" class="text-sm form-label">Recherche global </label>
                <input type="text" name="search" id="search" class="form-control" placeholder="----"
                    value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label for="status" class="form-label">Filtrer par statut</label>
                <select name="status" id="status" class="form-select">
                    <option value="">-- Tous les statuts --</option>
                    @foreach (['En attente', 'En cours', 'Résolue', 'Annulée'] as $status)
                        <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                            {{ $status }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label for="category" class="form-label">Filtrer par catégorie</label>
                <select name="category" id="category" class="form-select">
                    <option value="">-- Toutes les catégories --</option>
                    @foreach (['Materielle', 'Logicielle'] as $category)
                        <option value="{{ $category }}" {{ request('category') == $category ? 'selected' : '' }}>
                            {{ $category }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="gap-2 col-md-2 d-flex">
                <button type="submit" class="btn btn-outline-success" aria-label="Rechercher"><i
                        class="bi bi-search"></i></button>
                <a href="{{ route('pannes.index') }}" class="btn btn-outline-danger"
                    aria-label="Réinitialiser les filtres"><i class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="text-white bg-primary">
                    <tr>
                        <th scope="col">N° de panne</th>
                        <th scope="col">Signalé par</th>
                        <th scope="col">Équipement concerné</th>
                        <th scope="col">Description utilisateur</th>
                        <th scope="col">Résultat diagnostic</th>
                        <th scope="col">Catégorie panne</th>
                        <th scope="col">Statut panne</th>
                        <th scope="col">Action corrective</th>
                        <th scope="col">Date Signalisation</th>
                        <th scope="col">Date Traitement</th>
                        <th scope="col">Date Résolution</th>
                        <th scope="col">Date Annulation</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pannes as $panne)
                        <tr>
                            {{-- Numéro de panne basé sur la pagination --}}
                            <td class="text-nowrap">
                                {{ $loop->iteration + ($pannes->currentPage() - 1) * $pannes->perPage() }}
                            </td>
                            {{-- Nom et prénom de l'utilisateur qui a signalé la panne --}}
                            <td class="text-nowrap">
                                {{ $panne->user->nom_ag . ' ' . $panne->user->pren_ag }}
                            </td>
                            {{-- Numéro d'inventaire de l'équipement concerné --}}
                            <td class="text-nowrap">
                                {{ $panne->equipement ? $panne->equipement->num_inventaire_eq : 'N/A' }}
                            </td>
                            {{-- Description utilisateur de la panne --}}
                            <td class="text-nowrap">{{ $panne->lib_pan }}</td>
                            {{-- Résultat du diagnostic --}}
                            <td class="text-nowrap">{{ $panne->diag_pan }}</td>
                            {{-- Catégorie de la panne --}}
                            <td class="text-nowrap">{{ $panne->lib_cat }}</td>
                            {{-- Statut de la panne avec badge stylisé --}}
                            <td class="text-nowrap">
                                <span
                                    class="badge
                                    @if ($panne->sta_pan == 'En attente') bg-warning text-dark
                                    @elseif($panne->sta_pan == 'En cours') tw-bg-emerald-400 {{-- Assurez-vous que TailwindCSS est configuré pour cette classe --}}
                                    @elseif($panne->sta_pan == 'Résolue') bg-success
                                    @elseif($panne->sta_pan == 'Annulée') bg-danger @endif">
                                    {{ $panne->sta_pan }}
                                </span>
                            </td>
                            {{-- Action corrective --}}
                            <td class="text-nowrap">{{ $panne->action_pan ?? 'N/A' }} </td>
                            {{-- Date de signalisation --}}
                            <td class="text-nowrap">{{ $panne->date_signa->format('d/m/Y') }}</td>
                            {{-- Date de traitement --}}
                            <td class="text-nowrap">
                                @if ($panne->date_dt)
                                    {{ \Carbon\Carbon::parse($panne->date_dt)->format('d/m/Y') }}
                                @else
                                    -
                                @endif
                            </td>
                            {{-- Date de résolution ou d'annulation --}}
                            <td class="text-nowrap">
                                @if ($panne->date_rsl)
                                    {{ \Carbon\Carbon::parse($panne->date_rsl)->format('d/m/Y') }}
                                @else
                                    -
                                @endif
                            </td>

                            <td class="text-nowrap">
                                @if ($panne->date_an)
                                    {{ \Carbon\Carbon::parse($panne->date_an)->format('d/m/Y') }}
                                @else
                                    -
                                @endif
                            </td>
                            {{-- Colonne des actions --}}
                            <td class="text-nowrap">
                                {{-- Bouton Voir Détails --}}
                                <button type="button" class="mb-1 btn btn-outline-info btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#detailsModal{{ $panne->id }}" title="Voir détails">
                                    <i class="bi bi-eye"></i> Détails
                                </button>

                                {{-- Bouton Générer PDF (rapport d'historique de l'équipement) --}}
                                <a href="{{ route('reports.equipment.detail', $panne->equipement->id) }}" target="_blank"
                                    class="mb-1 btn btn-sm btn-outline-danger" title="Générer PDF">
                                    <i class="bi bi-clock-history"></i> Historique
                                </a>

                                @if ($panne->sta_pan == 'En attente')
                                    {{-- Bouton Valider (ouvre le modal de diagnostic) --}}
                                    <button type="button" class="mb-1 btn btn-outline-success btn-sm"
                                        data-bs-toggle="modal" data-bs-target="#diagPanModal{{ $panne->id }}"
                                        title="Valider cette panne">
                                        <i class="bi bi-check-circle"></i> Valider
                                    </button>

                                    {{-- Formulaire d'annulation et bouton --}}
                                    <form action="{{ route('pannes.update_status', $panne->id) }}" method="POST"
                                        class="d-inline" id="annulationForm{{ $panne->id }}">
                                        @csrf
                                        @method('PUT') {{-- Conversion en requête PUT --}}
                                        <input type="hidden" name="status" value="Annulée">
                                        <button type="button" class="mb-1 btn btn-outline-danger btn-sm annuler-btn"
                                            data-panne-id="{{ $panne->id }}" title="Annuler cette panne">
                                            <i class="bi bi-x-circle"></i> Rejéter
                                        </button>
                                    </form>
                                @elseif ($panne->sta_pan == 'En cours')
                                    {{-- Bouton Marquer comme résolue --}}
                                    <button type="button" class="mb-1 btn btn-outline-success btn-sm"
                                        data-bs-toggle="modal" data-bs-target="#actionPanModal{{ $panne->id }}"
                                        title="Marquer comme résolue">
                                        <i class="bi bi-check-all"></i> Résolue
                                    </button>

                                    {{-- Formulaire d'annulation et bouton (si la panne est en cours, elle peut aussi être annulée) --}}
                                    <form action="{{ route('pannes.update_status', $panne->id) }}" method="POST"
                                        class="d-inline" id="annulationForm{{ $panne->id }}">
                                        @csrf
                                        @method('PUT') {{-- Conversion en requête PUT --}}
                                        <input type="hidden" name="status" value="Annulée">
                                        <button type="button" class="mb-1 btn btn-outline-danger btn-sm annuler-btn"
                                            data-panne-id="{{ $panne->id }}" title="Annuler cette panne">
                                            <i class="bi bi-x-circle"></i> Annuler
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        {{-- Inclusion des modaux spécifiques à chaque panne --}}
                        @include('pages.pannes.modals.diagnostique', ['panne' => $panne])
                        @include('pages.pannes.modals.details', ['panne' => $panne])
                        @include('pages.pannes.modals.action', ['panne' => $panne])
                    @empty
                        <tr>
                            <td colspan="13" class="text-center text-muted">Aucune panne trouvée.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{-- Pagination --}}
        <div class="mt-4 d-flex justify-content-start">
            {{ $pannes->appends(request()->query())->links() }}
        </div>
    </div>

    {{-- Modal de confirmation global pour l'annulation (déplacé en dehors de la boucle) --}}
    <div class="modal fade" id="confirmationModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header ">
                    <h5 class="modal-title">Confirmer l'annulation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Êtes-vous sûr de vouloir annuler cette panne ?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Non</button>
                    <button type="button" class="btn btn-danger" id="confirmAnnulation">Oui, annuler</button>
                </div>
            </div>
        </div>
    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let formToSubmit = null; // Variable pour stocker le formulaire à soumettre

            // Attache un écouteur d'événements à tous les boutons d'annulation
            document.querySelectorAll('.annuler-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const panneId = this.getAttribute('data-panne-id');
                    formToSubmit = document.getElementById(
                        `annulationForm${panneId}`); // Stocke la référence au formulaire
                    // Affiche le modal de confirmation
                    const confirmationModal = new bootstrap.Modal(document.getElementById(
                        'confirmationModal'));
                    confirmationModal.show();
                });
            });
            // Attache un écouteur d'événements au bouton de confirmation dans le modal
            document.getElementById('confirmAnnulation').addEventListener('click', function() {
                if (formToSubmit) {
                    formToSubmit.submit(); // Soumet le formulaire stocké
                    // Cache le modal après soumission
                    const confirmationModal = bootstrap.Modal.getInstance(document.getElementById(
                        'confirmationModal'));
                    if (confirmationModal) {
                        confirmationModal.hide();
                    }
                }
            });
        });
    </script>
@endsection
