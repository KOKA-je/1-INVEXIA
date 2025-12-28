@extends('layouts.app')

@section('title', 'Édition de la catégorie')

@section('content')
    <div class="card">
        <div class="card-header">
            Modifier une catégorie d'équipement
        </div>

        <div class="card-body">
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('categories.update', $categorie->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="libelleCat" class="form-label">Catégorie de l'équipement</label>
                    <input type="text" name="lib_cat" class="form-control" id="libelleCat"
                        value="{{ old('lib_cat', $categorie->lib_cat) }}" required>
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('categories.index') }}" class="btn btn-secondary">Retour</a>
                    <button type="submit" class="btn btn-primary">Mettre à jour</button>
                </div>
            </form>
        </div>
    </div>
@endsection
