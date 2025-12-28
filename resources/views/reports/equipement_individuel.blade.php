<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: sans-serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 5px;
            font-size: 12px;
        }

        h1,
        h2 {
            text-align: center;
        }
    </style>
</head>

<body>
    <h1>{{ $title }}</h1>
    <p>Date de génération : {{ $date }}</p>

    <h2>Détails de l'équipement</h2>
    <ul>
        <li>Nom : {{ $equipement->nom_eq }}</li>
        <li>Catégorie : {{ $equipement->categorieEquipement->lib_cat ?? '-' }}</li>
        <li>Statut : {{ $equipement->statut_eq }}</li>
        <li>État : {{ $equipement->etat_eq }}</li>
        <li>Utilisateur actuel : {{ $equipement->user?->nom_ag }} {{ $equipement->user?->pren_ag }}</li>
    </ul>

    <h2>Historique des utilisateurs</h2>
    <ul>
        @forelse($utilisateurs as $user)
            <li>{{ $user->nom_ag }} {{ $user->pren_ag }}</li>
        @empty
            <li>Aucun utilisateur trouvé</li>
        @endforelse
    </ul>

    <h2>Graphique des pannes</h2>
    <img src="{{ $pannesChartUrl }}" alt="Graphique des pannes">

    <h2>Liste des pannes</h2>
    <table>
        <thead>
            <tr>
                <th>Libellé</th>
                <th>Description</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($equipement->pannes as $panne)
                <tr>
                    <td>{{ $panne->lib_pan }}</td>
                    <td>{{ $panne->desc_pan }}</td>
                    <td>{{ $panne->created_at->format('d/m/Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3">Aucune panne enregistrée</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>
