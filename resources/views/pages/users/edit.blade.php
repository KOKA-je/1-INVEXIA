@extends('layouts.app')

@section('title', 'Modifier un Utilisateur')

@section('voidgrubs')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Utilisateurs</a></li>
            <li class="breadcrumb-item active" aria-current="page">Édition d'utilisateurs</li>
        </ol>
    </nav>
@endsection

@section('content')

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3>Modifier l'utilisateur</h3>
        </div>
        <div class="card-body">
            <form action="{{ route('users.update', $user->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="mt-3 col-6">
                        <label for="mat_ag" class="form-label">Identifiant de l'utilisateur</label>
                        <input type="text" name="mat_ag" class="form-control" id="mat_ag"
                            value="{{ old('mat_ag', $user->mat_ag) }}" required>
                    </div>

                    <div class="mt-3 col-6">
                        <label for="nom_ag" class="form-label">Nom de l'utilisateur</label>
                        <input type="text" name="nom_ag" class="form-control" id="nom_ag"
                            value="{{ old('nom_ag', $user->nom_ag) }}" required>
                    </div>

                    <div class="mt-3 col-6">
                        <label for="pren_ag" class="form-label">Prénom de l'utilisateur</label>
                        <input type="text" name="pren_ag" class="form-control" id="pren_ag"
                            value="{{ old('pren_ag', $user->pren_ag) }}" required>
                    </div>

                    <div class="mt-3 col-6">
                        <label for="email" class="form-label">Email de l'utilisateur</label>
                        <input type="email" name="email" class="form-control" id="email"
                            value="{{ old('email', $user->email) }}" required>
                    </div>

                    <div class="mt-3 col-6">
                        <label for="password" class="form-label">Mot de passe</label>
                        <input type="password" name="password" class="form-control" id="password">
                        <small class="form-text text-muted">Laissez vide pour ne pas modifier le mot de passe.</small>
                    </div>

                    <div class="mt-3 col-6">
                        <label for="password_confirmation" class="form-label">Confirmer Mot de passe</label>
                        <input type="password" name="password_confirmation" class="form-control" id="password_confirmation">
                    </div>

                    <div class="mt-3 col-6">
                        <div class="form-group">
                            <label for="sta_ag" class="form-label">Statut utilisateur</label>
                            <select name="sta_ag" id="sta_ag" class="form-select" required>
                                <option value="">-- Choisir --</option>
                                <option value="actif" {{ old('sta_ag', $user->sta_ag) == 'actif' ? 'selected' : '' }}>Actif
                                </option>
                                <option value="inactif" {{ old('sta_ag', $user->sta_ag) == 'inactif' ? 'selected' : '' }}>
                                    Inactif</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-3 col-6">
                        <label for="dir_ag" class="form-label">Direction de l'utilisateur</label>
                        <input type="text" name="dir_ag" class="form-control" id="dir_ag"
                            value="{{ old('dir_ag', $user->dir_ag) }}" required>
                    </div>

                    <div class="mt-3 col-6">
                        <label for="loc_ag" class="form-label">Localisation de l'utilisateur</label>
                        <input type="text" name="loc_ag" class="form-control" id="loc_ag"
                            value="{{ old('loc_ag', $user->loc_ag) }}" required>
                    </div>

                    <div class="mt-3 col-6">
                        <label for="role_id" class="form-label">Rôle</label>
                        <select name="role_id" id="role_id" class="form-select" required>
                            @foreach ($roles as $role)
                                <option value="{{ $role->id }}"
                                    {{ $user->roles->first()?->id == $role->id ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-3 d-flex justify-content-between">
                    <button type="submit" class="btn btn-primary">Mettre à jour</button>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">Annuler</a>
                </div>
            </form>
        </div>
    </div>

@endsection
