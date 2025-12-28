@extends('layouts.app')

@section('title', 'Gestion des attributions')

@section('voidgrubs')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item active" aria-current="page">Attributions</li>
        </ol>
    </nav>
@endsection

@section('content')
    <div class="container">
        {{-- Filtres --}}
        <form method="GET" id="filterForm" class="mb-4 row g-4 align-items-end">
            <div class="col-sm-2">
                <label for="matricule" class="form-label">Matricule utilisateur</label>
                <input type="text" name="matricule" id="matricule" class="form-control" placeholder="xxxxx/xx"
                    value="{{ request('matricule') }}">
            </div>

            <div class="col-sm-2">
                <label for="num_inventaire" class="form-label">Numéro d'Inventaire</label>
                <input type="text" name="num_inventaire" id="num_inventaire" class="form-control" placeholder="xxxx/xx"
                    value="{{ request('num_inventaire') }}">
            </div>

            <div class="gap-2 col-sm-2 d-flex">
                <button type="submit" class="mt-4 btn btn-outline-success">
                    <i class="bi bi-search"></i>
                </button>
                <a href="{{ route('attributions.index') }}" class="mt-4 btn btn-outline-danger">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>

            <div class="gap-2 col-sm-3 d-flex">
                @can('create-attribution')
                    <a href="{{ route('attributions.create') }}" class="btn btn-secondary"> + Réaliser attribution</a>
                @endcan
                <button type="button" id="exportButton" class="btn btn-outline-success">
                    <i class="bi bi-file-earmark-spreadsheet"></i> Exporter
                </button>

            </div>
        </form>

        {{-- Tableau --}}
        <div class="table-responsive">
            <table class="table align-middle table-bordered table-hover">
                <thead class="text-white bg-primary">
                    <tr>
                        <th>#</th>
                        <th>Matricule propriétaire</th>
                        <th>Nom propriétaire</th>
                        <th>Prénom propriétaire</th>
                        <th>Equipement</th>
                        <th>Direction propriétaire</th>
                        <th>Localisation propriétaire</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php $index = 1; @endphp
                    @forelse ($attributions as $attribution)
                        @php
                            $groupedEquipements = $attribution->equipements->groupBy(
                                fn($eq) => $eq->categorieEquipement->lib_cat ?? 'Non défini',
                            );
                            $rowCount = $groupedEquipements->count();
                            $firstRow = true;
                        @endphp

                        @foreach ($groupedEquipements as $categorie => $equipementsParCategorie)
                            <tr>
                                @if ($firstRow)
                                    <td rowspan="{{ $rowCount }}">{{ $index++ }}</td>
                                    <td rowspan="{{ $rowCount }}">{{ $attribution->user->mat_ag }}</td>
                                    <td rowspan="{{ $rowCount }}">{{ $attribution->user->nom_ag }}</td>
                                    <td rowspan="{{ $rowCount }}">{{ $attribution->user->pren_ag }}</td>
                                @endif

                                <td class="text-nowrap">
                                    <strong>{{ $categorie }} :</strong>
                                    @foreach ($equipementsParCategorie as $equipement)
                                        <span class="mb-1 badge bg-warning d-inline-block">
                                            <i class="bi bi-cpu"></i> {{ $equipement->num_inventaire_eq }}
                                        </span>
                                    @endforeach
                                </td>

                                @if ($firstRow)
                                    <td rowspan="{{ $rowCount }}">{{ $attribution->user->dir_ag }}</td>
                                    <td rowspan="{{ $rowCount }}">{{ $attribution->user->loc_ag }}</td>
                                    <td rowspan="{{ $rowCount }}" class="text-nowrap">
                                        @can('view-attribution')
                                            <a href="{{ route('attributions.show', $attribution) }}"
                                                class="btn btn-outline-success btn-sm">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        @endcan

                                        {{-- Vérifie si date_retrait est vide avant d'afficher les boutons Modifier et Supprimer --}}
                                        @if (empty($attribution->date_retrait))
                                            @can('edit-attribution')
                                                <a href="{{ route('attributions.edit', $attribution) }}"
                                                    class="btn btn-outline-primary btn-sm">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            @endcan
                                            @can('delete-attribution')
                                                <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal"
                                                    data-bs-target="#deleteAttributionModal{{ $attribution->id }}">
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                            @endcan
                                        @else
                                            {{-- Affiche des boutons désactivés si la date_retrait est présente --}}
                                            @can('edit-attribution')
                                                <button class="btn btn-outline-primary btn-sm" disabled
                                                    title="Cette attribution a déjà été retirée.">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                            @endcan
                                            @can('delete-attribution')
                                                <button class="btn btn-outline-danger btn-sm" disabled
                                                    title="Cette attribution a déjà été retirée.">
                                                    <i class="bi bi-trash3"></i>
                                                </button>
                                            @endcan
                                        @endif
                                    </td>
                                    @php $firstRow = false; @endphp
                                @endif
                            </tr>
                        @endforeach
                        {{-- Modal suppression --}}
                        {{-- Assurez-vous que ce modal est bien inclus et fonctionne avec la logique de désactivation --}}
                        @include('pages.attributions.modals.delete', ['attribution' => $attribution])
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted">Aucune attribution trouvée.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-4 d-flex justify-content-start">
            {{ $attributions->appends(request()->query())->links() }}
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

        /* Styles pour les boutons désactivés, si nécessaire */
        .btn:disabled,
        .btn[disabled] {
            opacity: 0.6;
            cursor: not-allowed;
            box-shadow: none;
        }
    </style>
@endsection


@section('scripts')
    <script>
        document.getElementById('exportButton').addEventListener('click', function(e) {
            e.preventDefault();

            const form = document.getElementById('filterForm');
            const formData = new FormData(form);
            const params = new URLSearchParams();

            for (const [key, value] of formData.entries()) {
                if (value) {
                    params.append(key, value);
                }
            }

            let exportUrl = '{{ route('attributions.export') }}';
            if (params.toString()) {
                exportUrl += '?' + params.toString();
            }

            window.location.href = exportUrl;
        });
    </script>
@endsection
