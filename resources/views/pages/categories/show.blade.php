@extends('layouts.app')

@section('title', 'Détails de la catégorie d\'équipement')

@section('content')
    <div class="card">

        <div class=" card-body">

            <form>
                <div class="mt-3 row">
                    <div class="mt-3 col-12">
                        <div class="form-group">
                            <label class="form-label">Libellé de la catégorie</label>
                            <input type="text" class="form-control" readonly value="{{ $categorie->lib_cat }}">
                        </div>
                    </div>
                </div>
            </form>
            <div class="mt-3 d-flex justify-content-start">
                <a href="{{ route('categories.index') }}" class="btn btn-secondary">Retour à la liste</a>
            </div>



        </div>
    </div>
@endsection
