<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            margin: 20px;
            font-size: 10pt;
        }

        .header {
            position: relative;
            margin-bottom: 25px;
            padding-top: 10px;
            height: 70px;
            /* Ensures header has enough space */
        }

        .company-logo {
            position: absolute;
            left: 0;
            top: 0;
            height: 60px;
            /* Adjust based on your logo's aspect ratio */
            max-width: 200px;
            object-fit: contain;
            /* Prevents distortion if logo proportions vary */
        }

        .report-title {
            margin-left: 220px;
            /* Aligns title next to logo */
            padding-top: 15px;
            text-align: left;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
        }

        .date {
            font-size: 12px;
            color: #666;
        }

        h3 {
            font-size: 16px;
            margin-top: 25px;
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
            color: #333;
            /* Slightly darker heading color */
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
            vertical-align: top;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
            font-size: 10pt;
            /* Adjusted for better table fit */
            color: #555;
            /* Slightly darker header text */
        }

        td {
            font-size: 9pt;
        }

        .badge {
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 8pt;
            /* Slightly smaller for badges to fit better */
            color: white;
            display: inline-block;
            white-space: nowrap;
            /* Prevents badge text from wrapping */
        }

        .badge-warning {
            background-color: #ffc107;
        }

        .badge-danger {
            background-color: #dc3545;
        }

        .badge-success {
            background-color: #28a745;
        }

        ul.diagnostic-list {
            list-style-type: none;
            padding-left: 0;
            margin-bottom: 20px;
            font-size: 10pt;
        }

        ul.diagnostic-list li {
            padding: 4px 0;
            /* Slightly more vertical padding */
            line-height: 1.5;
        }

        .footer {
            position: fixed;
            bottom: 20px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8pt;
            color: #666;
            width: 100%;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    <div class="header">
        <img class="company-logo" src="data:image/png;base64,{{ $logo }}" alt="Logo Entreprise">
        <div class="report-title">
            <h2 class="title">{{ $title }}</h2>
            <small class="date">Généré le {{ $date }}</small>
        </div>
    </div>

    <h3>Statistiques Globales des Pannes</h3>
    <table>
        <thead>
            <tr>
                <th>Total Pannes</th>
                <th>En Attente</th>
                <th>En Cours</th>
                <th>Résolues</th>
                <th>Non Résolues</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>{{ $panneStats['total'] }}</strong></td>
                <td>{{ $panneStats['en_attente'] }}</td>
                <td>{{ $panneStats['en_cours'] }}</td>
                <td>{{ $panneStats['resolues'] }}</td>
                <td>{{ $panneStats['non_resolues_total'] ?? $panneStats['total'] - $panneStats['resolues'] }}</td>
            </tr>
        </tbody>
    </table>

    <h3>Diagnostics de Pannes les plus Fréquents</h3>
    @if (!empty($diagnosticsCounts))
        <table>
            <thead>
                <tr>
                    <th style="width: 70%;">Diagnostic</th>
                    <th style="width: 30%; text-align: center;">Nombre de fois</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($diagnosticsCounts as $diagnostic => $count)
                    <tr>
                        <td>{{ $diagnostic }}</td>
                        <td style="text-align: center;">{{ $count }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>Aucun diagnostic de panne enregistré pour le moment.</p>
    @endif

    <h3>Détails des {{ $pannes->count() }} Dernières Pannes</h3>
    @if ($pannes->isNotEmpty())
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Équipement</th>
                    <th>Catégorie</th>
                    <th>Diagnostic</th>
                    <th>Description</th>
                    <th>Statut</th>
                    <th>Signalé par (Matricule)</th>
                    <th>Traité par (Matricule)</th>
                    <th>Date Résolution</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pannes as $panne)
                    <tr>
                        <td>{{ $panne->date_signa ? \Carbon\Carbon::parse($panne->date_signa)->format('d/m/Y H:i') : 'N/A' }}
                        </td>
                        <td>{{ $panne->equipement->num_inventaire_eq ?? 'N/A' }}</td>
                        <td>{{ $panne->equipement->categorieEquipement->lib_cat ?? 'N/A' }}</td>
                        <td>{{ $panne->diag_pan ?? 'N/A' }}</td>
                        <td>{{ \Illuminate\Support\Str::limit($panne->lib_pan, 40) }}</td> {{-- Slightly shorter limit --}}
                        <td>
                            @if ($panne->sta_pan == 'En attente')
                                <span class="badge badge-warning">En attente</span>
                            @elseif($panne->sta_pan == 'En cours')
                                <span class="badge badge-danger">En cours</span>
                            @else
                                <span class="badge badge-success">Résolue</span>
                            @endif
                        </td>
                        <td>{{ $panne->user->mat_ag ?? 'N/A' }}</td> {{-- Reporter's matricule --}}
                        <td>{{ $panne->auteur->mat_ag ?? 'N/A' }}</td> {{-- Handler's matricule (auteur) --}}
                        <td>{{ $panne->date_rsl ? \Carbon\Carbon::parse($panne->date_rsl)->format('d/m/Y') : 'N/A' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>Aucune panne récente à afficher.</p>
    @endif


</body>

</html>
