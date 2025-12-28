@extends('layouts.app')
@section('title', 'Gestion des equipement')

@section('voidgrubs')
    @role('Super Admin')
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('equipements.index') }}">equipement</a></li>
                <li class="breadcrumb-item active" aria-current="page">Détails de equipement</li>
            </ol>
        </nav>
        @elserole('Admin')
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('equipements.index') }}">equipement</a></li>
                <li class="breadcrumb-item active" aria-current="page">Détails de equipement</li>
            </ol>
        </nav>
    @else
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('mon.espace') }}">Mes equipements</a></li>
                <li class="breadcrumb-item active" aria-current="page">Détails de equipement</li>
            </ol>
        </nav>
    @endrole

@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h4><strong>Détails de: {{ $equipement->nom_eq }}</strong></h4>
        </div>
        <div class="card-body">
            <form>
                <div class="row">
                    <div class="mt-3 col-6">
                        <label for="num_serie_eq" class="form-label">N° de série</label>
                        <input type="text" name="num_serie_eq" class="form-control" id="num_serie_eq"
                            value="{{ $equipement->num_serie_eq }}" disabled>
                    </div>

                    <div class="mt-3 col-6">
                        <label for="num_inventaire_eq" class="form-label">N° d'inventaire</label>
                        <input type="text" name="num_inventaire_eq" class="form-control" id="num_inventaire_eq"
                            value="{{ $equipement->num_inventaire_eq }}" disabled>
                    </div>

                    <div class="mt-3 col-6">
                        <label for="nom_eq" class="form-label">Nom du périphérique</label>
                        <input type="text" name="nom_eq" class="form-control" id="nom_eq"
                            value="{{ $equipement->nom_eq }}" disabled>
                    </div>

                    <div class="mt-3 col-6">
                        <label for="designation_eq" class="form-label">Désignation</label>
                        <input type="text" name="designation_eq" class="form-control" id="designation_eq"
                            value="{{ $equipement->designation_eq }}" disabled>
                    </div>

                    <div class="mt-3 col-6">
                        <label for="etat_eq" class="form-label">État</label>
                        <select name="etat_eq" class="form-select" disabled>
                            <option value="en panne" {{ $equipement->etat_eq == 'en panne' ? 'selected' : '' }}>En
                                panne</option>
                            <option value="en service" {{ $equipement->etat_equipement == 'en service' ? 'selected' : '' }}>
                                En service
                            </option>
                            <option value="Bon" {{ $equipement->etat_eq == 'Bon' ? 'selected' : '' }}>Bon
                            </option>
                        </select>
                    </div>

                    <div class="mt-3 col-6">
                        <label for="statu_eq" class="form-label">Statut</label>
                        <select name="statut_eq" class="form-select" disabled>
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
                        <input type="text" class="form-control" value="{{ $equipement->user->mat_ag ?? 'N/A' }}"
                            disabled>
                    </div>

                    <div class="mt-3 col-6">
                        <label class="form-label">Direction</label>
                        <input type="text" class="form-control" value="{{ $equipement->user->dir_ag ?? 'N/A' }}"
                            disabled>
                    </div>

                    <div class="mt-3 col-6">
                        <label class="form-label">Localisation</label>
                        <input type="text" class="form-control" value="{{ $equipement->user->loc_ag ?? 'N/A' }}"
                            disabled>
                    </div>


                    <div class="mt-3 col-6">
                        <label for="CategorieEquipement" class="form-label">Catégorie</label>
                        <select name="categorie_equipement_id" id="CategorieEquipement" class="form-select" disabled>
                            <option value="">-- Choisir --</option>
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
                            value="{{ \Carbon\Carbon::parse($equipement->date_acq)->format('Y-m-d') }}" disabled>
                    </div>

                    {{-- Section des boutons --}}
                    <div class="mt-4 col-12 d-flex justify-content-between align-items-center">
                        <div>
                            @if (Auth::user()->hasRole('Super Admin'))
                                <a href="{{ url()->previous() }}" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Retour
                                </a>
                            @elseif (Auth::user()->hasRole('Admin'))
                                <a href="{{ url()->previous() }}" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Retour
                                </a>
                            @else
                                <a href="{{ route('mon.espace') }}" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Retour
                                </a>
                            @endif
                        </div>

                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('select')
    {{-- Inclusion des bibliothèques jQuery et Select2 via CDN --}}
    {{-- Il est recommandé de charger ces scripts globalement dans layouts/app.blade.php pour éviter la duplication. --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.js-example-basic-single').select2();
        });
    </script>
@endsection
