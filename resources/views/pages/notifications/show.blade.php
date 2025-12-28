@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2>Détail de la notification</h2>
                <small class="text-muted">Reçue {{ $notification->created_at->diffForHumans() }}</small>
            </div>
            <div class="card-body">
                <h5 class="card-title">{{ $notification->data['message'] }}</h5>
                <p class="card-text">
                    <strong>Équipement:</strong> {{ $notification->data['equipement'] }}<br>
                    <strong>Libellé:</strong> {{ $notification->data['libelle'] }}
                </p>

                <div class="mt-4">
                    <a href="{{ route('pannes.show', $notification->data['panne_id']) }}" class="btn btn-primary">
                        Voir la panne associée
                    </a>
                    <form action="{{ route('notifications.destroy', $notification->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Supprimer cette notification</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
