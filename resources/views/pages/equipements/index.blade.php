@extends('layouts.app')

@section('title', 'Gestion des équipements de travail')

@section('voidgrubs')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active" aria-current="page">Équipements de travail</li>
        </ol>
    </nav>
@endsection

@section('content')
    <div class="container">
        {{-- Formulaire de recherche --}}
        <form method="GET" class="mb-4 row g-4 align-items-end" id="filterForm">
            <div class="col-sm-1">
                <label for="numero" class="form-label">N°Inventaire</label>
                <input type="text" name="numero" id="numero" class="form-control" placeholder="xxx/xx"
                    value="{{ request('numero') }}">
            </div>

            <div class="col-sm-2">
                <label for="etat_eq" class="form-label">État</label>
                <select name="etat_eq" id="etat_eq" class="form-control">
                    <option value="">-- --</option>
                    <option value="En service" {{ request('etat_eq') == 'En service' ? 'selected' : '' }}>En service
                    </option>
                    <option value="En panne" {{ request('etat_eq') == 'En panne' ? 'selected' : '' }}>En panne</option>
                    <option value="Bon" {{ request('etat_eq') == 'Bon' ? 'selected' : '' }}>Bon</option>
                </select>
            </div>

            <div class="col-sm-2">
                <label for="statut_eq" class="form-label">Statut</label>
                <select name="statut_eq" id="statut_eq" class="form-control">
                    <option value="">-- --</option>
                    <option value="attribué" {{ request('statut_eq') == 'attribué' ? 'selected' : '' }}>Attribué</option>
                    <option value="disponible" {{ request('statut_eq') == 'disponible' ? 'selected' : '' }}>Disponible
                    </option>
                    <option value="réformé" {{ request('statut_eq') == 'réformé' ? 'selected' : '' }}>Réformé</option>
                </select>
            </div>

            <div class="col-sm-2">
                <label for="direction" class="form-label">Direction</label>
                <input type="text" name="direction" id="direction" class="form-control"
                    placeholder="Ex : DSI, DRH, COMPTA" value="{{ request('direction') }}">
            </div>

            <div class="gap-1 col-sm-2 d-flex">
                <button type="submit" class="mt-4 btn btn-outline-success">
                    <i class="bi bi-search"></i>
                </button>
                <a href="{{ route('equipements.index') }}" class="mt-4 btn btn-outline-danger">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>

            <div class="gap-2 col-sm-3 d-flex">
                @can('create-equipement')
                    <a href="{{ route('equipements.create') }}" class="btn btn-secondary"> + Nouvel équipement</a>
                @endcan
                {{-- Modify this export button --}}
                <a href="#" id="exportButton" class="btn btn-outline-success">
                    <i class="bi bi-file-earmark-spreadsheet"></i> Exporter
                </a>
            </div>
        </form>
        {{-- Tableau responsive --}}
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="text-white bg-primary">
                    <tr>
                        <th>#</th>
                        <th>Nom et prénom propriétaire</th>
                        <th>Matricule</th>
                        <th>N° série</th>
                        <th>N° inventaire</th>
                        <th>Désignation</th>
                        <th>Catégorie</th>
                        <th>État</th>
                        <th>Statut</th>
                        <th>Direction</th>
                        <th>Localisation</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($equipements as $equipement)
                        <tr>
                            <td>{{ $loop->iteration + ($equipements->currentPage() - 1) * $equipements->perPage() }}</td>
                            <td>{{ $equipement->user?->nom_ag . ' ' . $equipement->user?->pren_ag ?? 'N/A' }}</td>
                            <td>{{ $equipement->user->mat_ag ?? 'N/A' }}</td>
                            <td>{{ $equipement->num_serie_eq }}</td>
                            <td>{{ $equipement->num_inventaire_eq }}</td>
                            <td>{{ $equipement->designation_eq }}</td>
                            <td>{{ $equipement->categorieEquipement->lib_cat ?? 'Non défini' }}</td>
                            <td>{{ $equipement->etat_eq }}</td>
                            <td>
                                <span
                                    class="badge
                                    {{ $equipement->statut_eq === 'réformé'
                                        ? 'bg-danger'
                                        : ($equipement->statut_eq === 'en service'
                                            ? 'bg-success'
                                            : ($equipement->statut_eq === 'disponible'
                                                ? 'bg-warning'
                                                : 'bg-secondary')) }}">
                                    {{ $equipement->statut_eq }}
                                </span>
                            </td>
                            <td>{{ $equipement->user->dir_ag ?? 'N/A' }}</td>
                            <td>{{ $equipement->user->loc_ag ?? 'N/A' }}</td>
                            <td class="text-nowrap">
                                @can('view-equipement')
                                    <a href="{{ route('equipements.show', $equipement->id) }}"
                                        class="btn btn-outline-success btn-sm"><i class="bi bi-eye"></i></a>
                                    <a href="{{ route('equipements.history', $equipement->id) }}"
                                        class="btn btn-outline-info btn-sm"><i class="bi bi-clock-history"></i></a>
                                @endcan
                                @if ($equipement->statut_eq !== 'réformé')
                                    @can('edit-equipement')
                                        <a href="{{ route('equipements.edit', $equipement->id) }}"
                                            class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i></a>
                                    @endcan
                                    @can('delete-equipement')
                                        <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal"
                                            data-bs-target="#deleteEquipementModal{{ $equipement->id }}">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    @endcan
                                @else
                                    @can('edit-equipement')
                                        <button class="btn btn-primary btn-sm disabled" aria-disabled="true"><i
                                                class="bi bi-pencil"></i></button>
                                    @endcan
                                    @can('delete-equipement')
                                        <button class="btn btn-danger btn-sm" disabled><i class="bi bi-trash3"></i></button>
                                    @endcan
                                @endif
                            </td>
                        </tr>
                        @include('pages.equipements.modals.delete', ['equipement' => $equipement])
                    @empty
                        <tr>
                            <td colspan="12" class="text-center text-muted">Aucun équipement trouvé.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{-- Pagination --}}
        <div class="mt-4 d-flex justify-content-start">
            {{ $equipements->appends(request()->query())->links() }}
        </div>
    </div>
@endsection

@section('style')
    <style>
        .btn {
            box-shadow: 0px 8px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.9s ease 0s;
            cursor: pointer;
            outline: none;
        }
    </style>
@endsection

@section('scripts')
    <script>
        document.getElementById('exportButton').addEventListener('click', function(e) {
            e.preventDefault(); // Prevent default link behavior
            const form = document.getElementById('filterForm');
            const formData = new FormData(form);
            const params = new URLSearchParams();
            for (const [key, value] of formData.entries()) {
                if (value) { // Only add parameters that have a value
                    params.append(key, value);
                }
            }
            let exportUrl = '{{ route('equipements.export') }}';
            if (params.toString()) {
                exportUrl += '?' + params.toString();
            }
            window.location.href = exportUrl;
        });
    </script>
@endsection
