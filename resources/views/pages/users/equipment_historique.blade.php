@extends('layouts.app')

@section('title', 'Gestion des accès et rôles')


@section('content')


    <h1>Equipment History for {{ $user->nom_ag }} {{ $user->pren_ag }}</h1>

    @if ($equipmentHistory->isEmpty())
        <p>No equipment history found for this user.</p>
    @else
        <table>
            <thead>
                <tr>
                    <th>Equipment Name</th>
                    <th>Serial Number</th>
                    <th>Assigned At</th>
                    <th>Returned At</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($equipmentHistory as $record)
                    <tr>
                        <td>{{ $record->equipment->name }}</td>
                        <td>{{ $record->equipment->serial_number }}</td>
                        <td>{{ $record->assigned_at ? $record->assigned_at->format('Y-m-d H:i') : 'N/A' }}</td>
                        <td>{{ $record->returned_at ? $record->returned_at->format('Y-m-d H:i') : 'Still Assigned' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <a href="{{ route('users.index') }}">Back to Users</a>


@endsection
