@extends('layouts.app')

@section('title', 'Gestion des attributions')

@section('voidgrubs')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active" aria-current="page">Historique d'attribution</li>
        </ol>
    </nav>
@endsection

@section('content')
    <div class="container">
        <form method="GET" class="mb-4 row g-3 align-items-end">
            <div class="col-md-3">
                <label for="beneficiaire_mat_ag" class="form-label">Matricule Bénéficiaire</label>
                <input type="text" name="beneficiaire_mat_ag" id="beneficiaire_mat_ag" class="form-control"
                    placeholder="Matricule Bénéficiaire" value="{{ request('beneficiaire_mat_ag') }}">
            </div>
            <div class="col-md-2">
                <label for="from" class="form-label">Date début</label>
                <input type="date" name="from" id="from" class="form-control" value="{{ request('from') }}">
            </div>
            <div class="col-md-2">
                <label for="to" class="form-label">Date fin</label>
                <input type="date" name="to" id="to" class="form-control" value="{{ request('to') }}">
            </div>
            <div class="col-md-2">
                <label for="action" class="form-label">Type d'action</label>
                <select name="action" id="action" class="form-select">
                    <option value="">Tous</option>
                    <option value="Création" {{ request('action') == 'Création' ? 'selected' : '' }}>Création</option>
                    <option value="Modification" {{ request('action') == 'Modification' ? 'selected' : '' }}>Modification
                    </option>
                    <option value="Retrait" {{ request('action') == 'Retrait' ? 'selected' : '' }}>Retrait</option>
                </select>
            </div>
            <div class="gap-2 col-md-2 d-flex">
                <button type="submit" class="btn btn-outline-success"><i class="bi bi-funnel"></i></button>
                <a href="{{ route('attributions.logs') }}" class="btn btn-outline-danger"><i
                        class="bi bi-arrow-counterclockwise"></i></a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Action</th>
                        {{-- <th>Auteur</th> --}}
                        <th>Bénéficiaire</th>
                        <th>Équipements</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td>{{ $log->id }}</td>
                            <td>{{ $log->action_type }}</td>
                            {{-- <td>
                                @if ($log->auteur)
                                    {{ $log->auteur->nom_ag }} {{ $log->auteur->pren_ag }}
                                @else
                                    Inconnu
                                @endif
                            </td> --}}
                            <td>
                                @if ($log->beneficiaire)
                                    {{ $log->beneficiaire->mat_ag }}
                                @else
                                    Inconnu
                                @endif
                            </td>
                            <td>
                                {{-- Récupération du type d'action --}}
                                @php
                                    $action = $log->action_type ?? '';
                                @endphp

                                {{-- Si action = Création → équipements attribués --}}
                                @if ($action === 'Création')
                                    @php
                                        $equipementsIds = collect();
                                        if (!empty($log->equipements)) {
                                            try {
                                                $ids = is_string($log->equipements)
                                                    ? json_decode($log->equipements, true)
                                                    : $log->equipements;

                                                $equipementsIds = \App\Models\Equipement::with('categorieEquipement')
                                                    ->whereIn('id', $ids)
                                                    ->get();
                                            } catch (Exception $e) {
                                            }
                                        }
                                    @endphp

                                    @if ($equipementsIds->isNotEmpty())
                                        <div><strong>Attribués :</strong></div>
                                        <ul>
                                            @foreach ($equipementsIds as $equipement)
                                                <li>
                                                    <strong>{{ $equipement->categorieEquipement->lib_cat ?? 'Sans catégorie' }}
                                                        :</strong>
                                                    <span class="badge bg-success">
                                                        {{ $equipement->nom_eq }}
                                                        ({{ $equipement->num_inventaire_eq ?? 'Sans N° inventaire' }})
                                                    </span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="text-muted">Aucun équipement attribué.</span>
                                    @endif
                                @endif

                                {{-- Si action = Modification → équipements ajoutés / retirés --}}
                                @if ($action === 'Modification')
                                    @php
                                        $equipementsAjoutes = collect();
                                        if (!empty($log->equipements_ajoutes)) {
                                            try {
                                                $ids = is_string($log->equipements_ajoutes)
                                                    ? json_decode($log->equipements_ajoutes, true)
                                                    : $log->equipements_ajoutes;

                                                $equipementsAjoutes = \App\Models\Equipement::with(
                                                    'categorieEquipement',
                                                )
                                                    ->whereIn('id', $ids)
                                                    ->get();
                                            } catch (Exception $e) {
                                            }
                                        }

                                        $equipementsRetires = collect();
                                        if (!empty($log->equipements_retires)) {
                                            try {
                                                $ids = is_string($log->equipements_retires)
                                                    ? json_decode($log->equipements_retires, true)
                                                    : $log->equipements_retires;

                                                $equipementsRetires = \App\Models\Equipement::with(
                                                    'categorieEquipement',
                                                )
                                                    ->whereIn('id', $ids)
                                                    ->get();
                                            } catch (Exception $e) {
                                            }
                                        }
                                    @endphp

                                    @if ($equipementsAjoutes->isNotEmpty())
                                        <div><strong>Ajoutés :</strong></div>
                                        <ul>
                                            @foreach ($equipementsAjoutes as $equipement)
                                                <li>
                                                    <strong>{{ $equipement->categorieEquipement->lib_cat ?? 'Sans catégorie' }}
                                                        :</strong>
                                                    <span class="badge tw-text-white tw-bg-green-400">
                                                        {{ $equipement->nom_eq }}
                                                        ({{ $equipement->num_inventaire_eq ?? 'Sans N° inventaire' }})
                                                    </span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif

                                    @if ($equipementsRetires->isNotEmpty())
                                        <div><strong>Retirés :</strong></div>
                                        <ul>
                                            @foreach ($equipementsRetires as $equipement)
                                                <li>
                                                    <strong>{{ $equipement->categorieEquipement->lib_cat ?? 'Sans catégorie' }}
                                                        :</strong>
                                                    <span class="badge tw-text-white tw-bg-pink-400">
                                                        {{ $equipement->nom_eq }}
                                                        ({{ $equipement->num_serie_eq ?? 'Sans N° série' }})
                                                    </span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif

                                    @if ($equipementsAjoutes->isEmpty() && $equipementsRetires->isEmpty())
                                        <span class="text-muted">Aucun changement d'équipements.</span>
                                    @endif
                                @endif

                                {{-- Récupération du type d'action --}}
                                @php
                                    $action = $log->action_type ?? '';
                                @endphp

                                {{-- Si action = Création → équipements attribués --}}
                                @if ($action === 'Retrait')
                                    @php
                                        $equipementsIds = collect();
                                        if (!empty($log->equipements_retires)) {
                                            try {
                                                $ids = is_string($log->equipements_retires)
                                                    ? json_decode($log->equipements_retires, true)
                                                    : $log->equipements_retires;

                                                $equipementsIds = \App\Models\Equipement::with('categorieEquipement')
                                                    ->whereIn('id', $ids)
                                                    ->get();
                                            } catch (Exception $e) {
                                            }
                                        }
                                    @endphp
                                    @if ($equipementsIds->isNotEmpty())
                                        <div><strong>Retiré :</strong></div>
                                        <ul>
                                            @foreach ($equipementsIds as $equipement)
                                                <li>
                                                    <strong>{{ $equipement->categorieEquipement->lib_cat ?? 'Sans catégorie' }}
                                                        :</strong>
                                                    <span class="badge bg-danger">
                                                        {{ $equipement->nom_eq }}
                                                        ({{ $equipement->num_serie_eq ?? 'Sans N° série' }})
                                                    </span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="text-muted">Aucun équipement attribué.</span>
                                    @endif
                                @endif
                            </td>
                            <td>
                                @php
                                    $date =
                                        $log->action_type === 'Création'
                                            ? $log->created_at->format('d/m/Y H:i')
                                            : $log->updated_at->format('d/m/Y H:i');
                                @endphp
                                {{ $date }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Il n'ya rien pour le moment.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 d-flex justify-content-start">
            {{ $logs->links() }}
        </div>
    </div>
@endsection
