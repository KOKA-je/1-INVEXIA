@foreach ($pannes as $panne)
    <!-- Modal Détails -->
    <div class="modal fade" id="detailsModal{{ $panne->id }}" tabindex="-1"
        aria-labelledby="detailsModalLabel{{ $panne->id }}" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="border-0 shadow-lg modal-content rounded-4">
                <div class="text-white modal-header tw-bg-orange-200 rounded-top-4">
                    <h5 class="modal-title" id="detailsModalLabel{{ $panne->id }}">
                        <i class="bi bi-exclamation-circle-fill me-2"></i>
                        Détails de la panne N°{{ $panne->id }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Fermer"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-4">
                        <!-- Informations de base -->
                        <div class="col-md-6">
                            <div class="border-0 shadow-sm card h-100">
                                <div class="card-header bg-light fw-semibold">Informations de la panne</div>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item"><strong>Description panne par l'utilisateur :</strong>
                                        {{ $panne->lib_pan }}</li>
                                    {{-- <li class="list-group-item"><strong>Catégorie :</strong> {{ $panne->lib_cat }}</li> --}}
                                    <li class="list-group-item">
                                        <strong>Statut :</strong>
                                        <span
                                            class="badge
                                        @if ($panne->sta_pan == 'En attente') bg-warning text-dark
                                        @elseif($panne->sta_pan == 'En cours') bg-info text-dark
                                        @elseif($panne->sta_pan == 'Résolue') bg-success
                                        @elseif($panne->sta_pan == 'Annulée') bg-danger @endif">
                                            {{ $panne->sta_pan }}
                                        </span>
                                    </li>

                                    <li class="list-group-item"><strong>Diagnostic :</strong>
                                        {{ $panne->diag_pan ?? 'Non spécifié' }}</li>
                                    <li class="list-group-item"><strong>Auteur diagnostique :</strong>
                                        @if ($panne->sta_pan == 'En attente')
                                            Non définis
                                        @else
                                            {{ $panne->auteur->mat_ag ?? 'Non spécifié' }}
                                        @endif
                                    </li>
                                    <li class="list-group-item"><strong>Action menée :</strong>
                                        {{ $panne->action_pan ?? '-' }}</li>
                                </ul>
                            </div>
                        </div>
                        <!-- Dates -->
                        <div class="col-md-6">
                            <div class="border-0 shadow-sm card h-100">
                                <div class="card-header bg-light fw-semibold">Période d'action </div>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item"><strong>signalement de panne :</strong>
                                        {{ $panne->date_signa->format('d/m/Y') }}</li>
                                    <li class="list-group-item">
                                        <strong>debut de traitement :</strong>
                                        {{ $panne->date_dt ? \Carbon\Carbon::parse($panne->date_dt)->format('d/m/Y ') : 'Non traité' }}
                                    </li>
                                    <li class="list-group-item">
                                        @if ($panne->sta_pan == 'Résolue' && $panne->date_rsl)
                                            <strong>Résolution</strong>
                                            {{ \Carbon\Carbon::parse($panne->date_rsl)->format('d/m/Y ') }}
                                        @elseif($panne->sta_pan == 'Annulée' && $panne->date_an)
                                            <strong>Annulation de traitement</strong>
                                            {{ \Carbon\Carbon::parse($panne->date_an)->format('d/m/Y') }}
                                        @else
                                            -
                                        @endif
                                    </li>
                                </ul>
                            </div>
                        </div>

                        <!-- Équipement concerné -->
                        <div class="col-md-6">
                            <div class="border-0 shadow-sm card h-100">
                                <div class="card-header bg-light fw-semibold">Équipement concerné</div>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item"><strong>Numéro d'inventaire :</strong>
                                        {{ $panne->equipement->num_inventaire_eq ?? 'N/A' }}</li>
                                    <li class="list-group-item"><strong>État :</strong>
                                        {{ $panne->equipement->etat_eq ?? 'N/A' }}</li>

                                    <li class="list-group-item"><strong>Localisation :</strong>
                                        {{ $panne->user->loc_ag ?? 'N/A' }}</li>
                                    <li class="list-group-item"><strong>Date acquisition :</strong>
                                        {{ \Carbon\Carbon::parse($panne->equipement->date_acq)->format('d/m/Y') }}</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Signalement -->
                        <div class="col-md-6">
                            <div class="border-0 shadow-sm card h-100">
                                <div class="card-header bg-light fw-semibold"> Infos Propriétaire </div>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item"><strong>Matricule :</strong>
                                        {{ $panne->user->mat_ag ?? 'N/A' }}</li>
                                    <li class="list-group-item"><strong>Nom :</strong>
                                        {{ $panne->user->nom_ag ?? 'N/A' }}</li>
                                    <li class="list-group-item"><strong>Prenom :</strong>
                                        {{ $panne->user->pren_ag ?? 'N/A' }}</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-1"></i> Fermer
                    </button>
                </div>
            </div>
        </div>
    </div>
@endforeach
