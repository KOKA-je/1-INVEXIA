@extends('layouts.app')

@section('title', 'Gestion des equipements')
@section('voidgrubs')

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('categories.index') }}">Type poste</a></li>
            <li class="breadcrumb-item active" aria-current="page">Creation de type poste</li>
        </ol>
    </nav>
@endsection

@section('content')
    <div class="card">

        <div class="card-body">

            <form action="{{ route('categories.store') }}" method="POST">

                @csrf

                <div class="mt-3 col-12">

                    <label for="lib_cat" class="form-label">Catégorie de l'équipement</label>
                    <input type="text" name="lib_cat" class="form-control" id="lib_cat" required>

                </div>

                <div class="mt-3">

                    <button type="submit" class="btn btn-primary">Créer</button>
                    <a href="{{ route('categories.index') }}" class="btn btn-secondary">Retour</a>

                </div>

            </form>


        </div>
    </div>
@endsection
