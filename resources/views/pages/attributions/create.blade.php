@extends('layouts.app')

@section('title', 'Gestion des attributions')

@section('voidgrubs')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('attributions.index') }}">Attribution</a></li>
            <li class="breadcrumb-item active" aria-current="page">Creation d'attribution</li>
        </ol>
    </nav>
@endsection

@section('content')

    <div class="card">
        <div class="card-header bg-light d-flex justify-content-start align-items-center">
            <div>
                <p>Nouvelle attribution</p>
            </div>
        </div>
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('attributions.store') }}" method="POST">
                @csrf
                <div class="mt-1 row">

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

                    <div class="mt-3 col-12">
                        <div class="form-group">
                            <label for="equipements">Equipement</label>
                            <select name="equipements[]" id="equipements" class="js-example-basic-multiple form-control"
                                multiple>
                                @foreach ($equipements as $equipement)
                                    <option value="{{ $equipement->id }}"
                                        {{ in_array($equipement->id, old('equipements', [])) ? 'selected' : '' }}>
                                        {{ $equipement->num_inventaire_eq }} - {{ $equipement->nom_eq }}
                                        @if ($equipement->categorieEquipement)
                                            ({{ $equipement->categorieEquipement->lib_cat }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mt-3 col-12">
                        <label for="date_attribution" class="form-label">Date d'attribution</label>
                        <input type="date" name="date_attribution" class="form-control" id="date_attribution"
                            value="{{ old('date_attribution', date('Y-m-d')) }}" required>
                    </div>
                    <div class="mt-3 d-flex justify-content-between">
                        <button type="submit" class="btn btn-primary">Valider</button>
                        <a href="{{ route('attributions.index') }}" class="btn btn-secondary">Annuler</a>
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
            $('.js-example-basic-multiple').select2();
        });
    </script>
@endsection
