@extends('layouts.app')

@section('title', 'Gestion des Equipements')
@section('voidgrubs')

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('categories.index') }}">Type peripherique</a></li>

        </ol>
    </nav>
@endsection

@section('content')

    <div class="container mt-4">

        {{-- Formulaire de recherche --}}
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label for="libelle" class="form-label">Catégorie</label>
                <input categorie="text" name="libelle" id="libelle" class="form-control" placeholder="Ex: Clavier, Souris"
                    value="{{ request('libelle') }}">
            </div>

            <div class="col-md-2">
                <label for="created_at" class="form-label">Créé le</label>
                <input categorie="date" name="created_at" id="created_at" class="form-control"
                    value="{{ request('created_at') }}" placeholder="x/x/x">
            </div>

            <div class="col-md-2">
                <label for="updated_at" class="form-label">Mis à jour le</label>
                <input categorie="date" name="updated_at" id="updated_at" class="form-control"
                    value="{{ request('updated_at') }}" placeholder="x/x/x">
            </div>

            <div class="gap-2 col-md-2 d-flex">
                <button categorie="submit" class="mt-4 btn btn-outline-success">
                    <i class="bi bi-search"></i>
                </button>
                <a href="{{ route('categories.index') }}" class="mt-4 btn btn-outline-danger">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>

            <div class="gap-2 col-md-4 d-flex">
                @can('create-categorie-peripherique')
                    <a href="{{ route('categories.create') }}" class="btn btn-secondary">Nouvelle catégorie</a>
                @endcan

            </div>
        </form>

        {{-- Tableau des types de périphériques --}}
        <div class="mt-4 table-responsive">
            <table class="table table-bordered table-hover">
                <thead class="text-white bg-primary">
                    <tr>
                        <th>N°</th>
                        <th>Catégorie d'équipement</th>
                        <th>Créé le</th>
                        <th>Mis à jour le</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $categorie)
                        <tr>
                            <td>{{ $loop->iteration + ($categories->currentPage() - 1) * $categories->perPage() }}</td>
                            <td>{{ $categorie->lib_cat }}</td>
                            <td>{{ $categorie->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $categorie->updated_at->format('d/m/Y H:i') }}</td>
                            <td>


                                <a href="{{ route('categories.show', $categorie->id) }}"
                                    class="btn btn-outline-success btn-sm"><i class="bi bi-eye"></i></a>




                                <a href="{{ route('categories.edit', $categorie->id) }}"
                                    class="btn btn-outline-primary btn-sm"><i class="bi bi-pencil"></i></a>




                                <button class="btn btn-outline-danger btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#deleteCategorieModal{{ $categorie->id }}">
                                    <i class="bi bi-trash3"></i>
                                </button>


                            </td>
                        </tr>
                        @include('pages.categories.modals.delete', ['categorie' => $categorie])
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">Aucun categorie de périphérique trouvé.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="mt-4 d-flex justify-content-start">
            {{ $categories->appends(request()->query())->links() }}
        </div>
    </div>

@endsection

@section('style')
    <style>
        .btn {
            box-shadow: 0px 8px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease-in-out;
            cursor: pointer;
        }
    </style>
@endsection
