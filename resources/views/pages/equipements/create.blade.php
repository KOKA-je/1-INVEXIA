@extends('layouts.app')
@section('title', 'Gestion des équipements')

@section('voidgrubs')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('equipements.index') }}">Équipements</a></li>
            <li class="breadcrumb-item active" aria-current="page">Création d'un équipement</li>
        </ol>
    </nav>
@endsection

@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $message)
                <li>{{ $message }}</li>
            @endforeach
        </ul>
    </div>
@endif

@section('content')

    <div class="mt-4 card">
        <div class="card-header">Importer des équipements via excel</div>
        <div class="card-body">
            <form action="{{ route('equipements.import') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mt-2 mb-3">
                    <label for="file" class="form-label">Fichier Excel (.xlsx, .xls, .csv)</label>
                    <input type="file" name="file" id="file" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-success">Importer</button>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <p class="mb-0">Créer un nouvel équipement</p>
        </div>
        <div class="card-body">
            <form action="{{ route('equipements.store') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="mt-3 col-6">
                        <label for="num_serie_eq" class="form-label">Numéro de série</label>
                        <input type="text" name="num_serie_eq" class="form-control" id="num_serie_eq" required>
                    </div>

                    <div class="mt-3 col-6">
                        <label for="num_inventaire_eq" class="form-label">Numéro d'inventaire</label>
                        <input type="text" name="num_inventaire_eq" class="form-control" id="num_inventaire_eq" required>
                    </div>

                    <div class="mt-3 col-6">
                        <label for="nom_eq" class="form-label">Nom de l’équipement</label>
                        <input type="text" name="nom_eq" class="form-control" id="nom_eq" required>
                    </div>

                    <div class="mt-3 col-6">
                        <label for="designation_eq" class="form-label">Désignation</label>
                        <input type="text" name="designation_eq" class="form-control" id="designation_eq">
                    </div>


                    <div class="mt-3 col-12">
                        <div class="form-group">
                            <label for="user_id">Agent Bénéficiaire</label>
                            <select name="user_id" id="user_id" class="js-example-basic-single form-control" required>
                                <option value="">Sélectionner un agent</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->mat_ag }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>


                    <div class="mt-3 col-6">
                        <label for="etat_eq" class="form-label">État</label>
                        <select name="etat_eq" id="etat_eq" class="js-example-basic-single form-select">
                            <option value="">-- Choisir --</option>
                            <option value="Bon">Bon</option>
                            <option value="en panne">En panne</option>
                            <option value="en service">En service</option>
                        </select>
                    </div>

                    <div class="mt-3 col-6">
                        <label for="statut_eq" class="form-label">Statut</label>
                        <select name="statut_eq" id="statut_eq" class="js-example-basic-single form-select">
                            <option value="">-- Choisir --</option>
                            <option value="attribué">Attribué</option>
                            <option value="disponible">Disponible</option>
                        </select>
                    </div>

                    <div class="mt-3 col-md-6">
                        <label for="categorie_equipement_id" class="form-label">Catégorie d'équipement</label>
                        <select name="categorie_equipement_id" id="categorie_equipement_id" class="form-select select2"
                            required>
                            <option value="" selected disabled>-- Sélectionnez une catégorie --</option>
                            @foreach ($cats as $cat)
                                <option value="{{ $cat->id }}" @selected(old('categorie_equipement_id') == $cat->id)>
                                    {{ $cat->lib_cat }}
                                </option>
                            @endforeach
                        </select>
                        @error('categorie_equipement_id')
                            <div class="text-danger small">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mt-3 col-6">
                        <label for="date_acq" class="form-label">Date d'acquisition</label>
                        <input type="date" name="date_acq" class="form-control" id="date_acq">
                    </div>

                    <div class="mt-4 d-flex justify-content-between">
                        <button type="submit" class="btn btn-primary">Créer</button>
                        <a href="{{ route('equipements.index') }}" class="btn btn-secondary">Annuler</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('select')
    <script>
        $(document).ready(function() {
            $('.js-example-basic-single').select2({
                width: '100%',
                dropdownPosition: 'below'
            });

            // Fonction de mise à jour automatique des champs
            function updateFieldsBasedOnUser() {
                let userSelected = $('#user_id').val();

                if (userSelected) {
                    $('#statut_eq').val('attribué').trigger('change');
                    $('#etat_eq').val('en service').trigger('change');
                } else {
                    $('#statut_eq').val('disponible').trigger('change');
                    $('#etat_eq').val('Bon').trigger('change');
                }

                // Toujours désactiver les champs
                $('#statut_eq').prop('readonly', true);
                $('#etat_eq').prop('readonly', true);
            }

            // Appel initial (au cas où old() a prérempli le user)
            updateFieldsBasedOnUser();

            // Mise à jour quand user change
            $('#user_id').on('change', updateFieldsBasedOnUser);
        });
    </script>
@endsection
