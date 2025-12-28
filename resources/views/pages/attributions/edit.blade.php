@extends('layouts.app')

@section('title', 'Gestion des attributions')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('attributions.index') }}">Attributions</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edition d'attribution</li>
        </ol>
    </nav>
@endsection

@section('content')
    <div class="card">
        <div class="card-header bg-light d-flex justify-content-start align-items-center">
            <div>
                <p>Modifier l'attribution</p>
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('attributions.update', $attribution) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mt-1 row">
                    <div class="mt-3 col-12">
                        <div class="form-group">
                            <label for="user_id">Utilisateur Bénéficiaire</label>
                            <select name="user_id" id="user_id" class="js-example-basic-single form-control">
                                <option value="">Sélectionner un utilisateur</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}"
                                        {{ $attribution->user_id == $user->id ? 'selected' : '' }}>
                                        {{ $user->mat_ag }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mt-3 col-12">
                        <div class="form-group">
                            <label for="equipements">Équipements attribués</label>
                            <select name="equipements[]" id="equipements" class="js-example-basic-multiple form-control"
                                multiple>
                                @foreach ($attribution->equipements as $equipement)
                                    <option value="{{ $equipement->id }}" selected>
                                        @if ($equipement->categorieEquipement)
                                            {{ $equipement->categorieEquipement->lib_cat }} -
                                            {{ $equipement->num_inventaire_eq }}
                                        @else
                                            {{ $equipement->num_inventaire_eq }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mt-3 col-12">
                        <div class="form-group">
                            <label for="equipements1">Ajouter Équipements</label>
                            <select name="equipements[]" id="equipements1" class="js-example-basic-multiple form-control"
                                multiple>
                                @foreach ($equipements as $equipement)
                                    <option value="{{ $equipement->id }}">
                                        @if ($equipement->categorieEquipement)
                                            {{ $equipement->categorieEquipement->lib_cat }} -
                                            {{ $equipement->num_inventaire_eq }}
                                        @else
                                            {{ $equipement->num_inventaire_eq }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>


                    <div class="mt-3 col-12">
                        <label for="date_attribution" class="form-label">Date d'attribution</label>
                        <input type="date" name="date_attribution" class="form-control" id="date_attribution"
                            value="{{ $attribution->date_attribution->format('Y-m-d') }}" required>
                    </div>

                    <div class="mt-3 col-12">
                        <label for="date_retrait" class="form-label">Date de retrait (optionnel)</label>
                        <input type="date" name="date_retrait" class="form-control" id="date_retrait"
                            value="{{ $attribution->date_retrait ? $attribution->date_retrait->format('Y-m-d') : '' }}">
                    </div>

                    <div class="mt-3 d-flex justify-content-between">
                        <button type="submit" class="btn btn-primary">Mettre à jour</button>
                        <a href="{{ route('attributions.index') }}" class="btn btn-secondary">Annuler</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('.js-example-basic-single').select2();
            $('.js-example-basic-multiple').select2();
        });
    </script>
@endsection
