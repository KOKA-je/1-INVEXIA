@extends('layouts.app')
@section('title', 'Gestion des Equipements')

@section('voidgrubs')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('equipements.index') }}">equipement</a></li>
            <li class="breadcrumb-item active" aria-current="page">Modification d'equipement</li>
        </ol>
    </nav>
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h4><strong>Modifier: {{ $equipement->nom_eq }}</strong></h4>
        </div>
        <div class="card-body">
            <form action="{{ route('equipements.update', $equipement->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="row">
                    <div class="mt-3 col-6">
                        <label for="num_serie_eq" class="form-label">N° de série</label>
                        <input type="text" name="num_serie_eq" class="form-control" id="num_serie_eq"
                            value="{{ $equipement->num_serie_eq }}">
                    </div>

                    <div class="mt-3 col-6">
                        <label for="num_inventaire_eq" class="form-label">N° d'inventaire</label>
                        <input type="text" name="num_inventaire_eq" class="form-control" id="num_inventaire_eq"
                            value="{{ $equipement->num_inventaire_eq }}">
                    </div>

                    <div class="mt-3 col-6">
                        <label for="nom_eq" class="form-label">Nom du périphérique</label>
                        <input type="text" name="nom_eq" class="form-control" id="nom_eq"
                            value="{{ $equipement->nom_eq }}">
                    </div>

                    <div class="mt-3 col-6">
                        <label for="designation_eq" class="form-label">Désignation</label>
                        <input type="text" name="designation_eq" class="form-control" id="designation_eq"
                            value="{{ $equipement->designation_eq }}">
                    </div>

                    <div class="mt-3 col-6">
                        <label for="etat_eq" class="form-label">État</label>
                        <select name="etat_eq" class="form-select">
                            <option value="en panne" {{ $equipement->etat_eq == 'en panne' ? 'selected' : '' }}>En
                                panne</option>
                            <option value="en service" {{ $equipement->etat_eq == 'en service' ? 'selected' : '' }}>En
                                service
                            </option>
                            <option value="Bon" {{ $equipement->etat_eq == 'Bon' ? 'selected' : '' }}>Bon
                            </option>
                        </select>
                    </div>

                    <div class="mt-3 col-6">
                        <label for="statu_eq" class="form-label">Statut</label>
                        <select name="statut_eq" class="form-select">
                            <option value="disponible" {{ $equipement->statut_eq == 'disponible' ? 'selected' : '' }}>
                                Disponible
                            </option>
                            <option value="attribué" {{ $equipement->statut_eq == 'attribué' ? 'selected' : '' }}>
                                Attribué</option>
                            <option value="réformé" {{ $equipement->statut_eq == 'reformé' ? 'selected' : '' }}>
                                Reformé</option>
                        </select>
                    </div>

                    <div class="mt-3 col-6">
                        <label class="form-label">Propriétaire</label>
                        <input type="text" class="form-control" value="{{ $equipement->user->mat_ag ?? 'N/A' }}">
                    </div>

                    <div class="mt-3 col-6">
                        <label class="form-label">Direction</label>
                        <input type="text" class="form-control" value="{{ $equipement->user->dir_ag ?? 'N/A' }}">
                    </div>

                    <div class="mt-3 col-6">
                        <label class="form-label">Localisation</label>
                        <input type="text" class="form-control" value="{{ $equipement->user->loc_ag ?? 'N/A' }}">
                    </div>

                    <div class="mt-3 col-6">
                        <label for="CategorieEquipement" class="form-label">Catégorie</label>
                        <select name="categorie_equipement_id" id="CategorieEquipement" class="form-select">
                            @foreach ($categories as $categorie)
                                <option value="{{ $categorie->id }}"
                                    {{ $equipement->categorie_equipement_id == $categorie->id ? 'selected' : '' }}>
                                    {{ $categorie->lib_cat }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mt-3 col-6">
                        <label for="date_acq" class="form-label">Date d'acquisition</label>
                        <input type="date" name="date_acq" class="form-control" id="date_acq"
                            value="{{ \Carbon\Carbon::parse($equipement->date_acq)->format('Y-m-d') }}">
                    </div>

                    <div class="mt-3 d-flex justify-content-between">
                        <button type="submit" class="btn btn-primary">Valider</button>
                        <a href="{{ route('equipements.index') }}" class="btn btn-secondary">Retour</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('select')
    <script>
        $(document).ready(function() {
            $('.js-example-basic-single').select2();
        });
    </script>
@endsection
