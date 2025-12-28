@extends('layouts.app')

@section('title', "Historique de l'équipement : " . $equipement->num_inventaire_eq)

@section('voidgrubs')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('equipements.index') }}">Équipements</a></li>
            <li class="breadcrumb-item active" aria-current="page">Historique</li>
        </ol>
    </nav>
@endsection
@section('content')
    <div class="container">
        <h2> N° Série de l'équipement: {{ $equipement->num_serie_eq }}
        </h2>
        <hr>
        <a href="{{ route('equipements.index', $equipement->id) }}" class="mb-4 btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
        @if ($history->isEmpty())
            <div class="text-center card">
                <div class="card-body">
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="#ccc" class="mb-3"
                        viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48
                                                        10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8
                                                        8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z" />
                    </svg>
                    <p class="text-muted">Il n'y a rien pour le moment.</p>
                </div>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="text-white bg-primary">
                        <tr>
                            <th>Date de l'évènement</th>
                            <th>Evènement</th>
                            <th>Détails</th>
                            <th>statut</th>
                            <th>état</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($history as $record)
                            <tr>
                                <td class="text-nowrap">{{ $record->created_at->format('d/m/Y H:i') }}</td>
                                <td class="text-nowrap">{{ $record->action }}</td>
                                <td>{{ $record->details ?? 'N/A' }}</td>
                                <td>{{ $record->new_status ?? 'N/A' }}</td>
                                <td>{{ $record->new_state ?? 'N/A' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection

@section('style')
    <style>
        .btn {
            box-shadow: 0px 8px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.9s ease 0s;
            cursor: pointer;
            outline: none;
        }
    </style>
@endsection
