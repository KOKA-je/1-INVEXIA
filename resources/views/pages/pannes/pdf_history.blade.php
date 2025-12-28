<!DOCTYPE html>
<html>

<head>
    <title>Historique des Pannes de {{ $equipementName }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .diagnostics-section {
            margin-top: 30px;
        }

        .diagnostics-section h2 {
            font-size: 14px;
            margin-bottom: 10px;
        }

        .diagnostics-section ul {
            list-style: none;
            padding: 0;
        }

        .diagnostics-section li {
            margin-bottom: 5px;
        }
    </style>
</head>

<body>
    <h1>Historique des Pannes de l'équipement : {{ $equipementName }}</h1>

    <table>
        <thead>
            <tr>
                <th>Libellé Panne</th>
                <th>Description</th>
                <th>Date Début</th>
                <th>Date Fin</th>
                <th>Statut</th>
                <th>Catégorie</th>
                <th>Diagnostic</th>
                <th>Rapporteur</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pannesForPdf as $panne)
                <tr>
                    <td>{{ $panne->lib_pan }}</td>
                    <td>{{ $panne->desc_pan ?? 'N/A' }}</td>
                    <td>{{ $panne->date_deb_pan ? \Carbon\Carbon::parse($panne->date_deb_pan)->format('d/m/Y H:i') : 'N/A' }}
                    </td>
                    <td>{{ $panne->date_fin_pan ? \Carbon\Carbon::parse($panne->date_fin_pan)->format('d/m/Y H:i') : 'N/A' }}
                    </td>
                    <td>{{ $panne->sta_pan }}</td>
                    <td>{{ $panne->lib_cat }}</td>
                    <td>{{ $panne->diag_pan ?? 'N/A' }}</td>
                    <td>{{ $panne->user ? $panne->user->nom_ag . ' ' . $panne->user->pren_ag : 'N/A' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">Aucune panne trouvée pour cet équipement.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if (!empty($diagnosticsCounts))
        <div class="diagnostics-section">
            <h2>Statistiques des Diagnostics les Plus Fréquents</h2>
            <ul>
                @foreach ($diagnosticsCounts as $diagnostic => $count)
                    <li><strong>{{ $diagnostic }}</strong>: {{ $count }} fois</li>
                @endforeach
            </ul>
        </div>
    @endif
</body>

</html>
