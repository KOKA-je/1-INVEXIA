<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        .header {
            margin-bottom: 25px;
            position: relative;
        }

        .logo {
            height: 60px;
            position: absolute;
            left: 0;
            top: 0;
        }

        .title {
            margin-left: 220px;
        }

        .badge {
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 12px;
            color: white;
            display: inline-block;
        }

        .badge-success {
            background-color: #28a745;
        }

        .badge-warning {
            background-color: #ffc107;
            color: #212529;
        }

        .badge-danger {
            background-color: #dc3545;
        }

        .badge-info {
            background-color: #17a2b8;
        }

        .info-card {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 11px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 6px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 10px;
            margin-bottom: 15px;
        }

        .stat-item {
            border: 1px solid #eee;
            padding: 10px;
            border-radius: 5px;
            background-color: #f9f9f9;
        }

        .stat-value {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
        }

        .stat-label {
            font-size: 11px;
            color: #7f8c8d;
        }

        .page-break {
            page-break-after: always;
        }

        .text-center {
            text-align: center;
        }

        .diagnostic-item {
            margin-bottom: 5px;
            padding-left: 10px;
            border-left: 3px solid #3498db;
        }
    </style>
</head>

<body>
    <!-- En-tête avec logo -->
    <div class="header">
        @if ($logo)
            <img class="logo" src="data:image/png;base64,{{ $logo }}" alt="Logo">
        @endif
        <div class="title">
            <h1 style="margin: 0 0 5px 0;">{{ $title }}</h1>
            <small>Généré le {{ $report_date }}</small>
        </div>
    </div>
    <hr>
    <!-- Carte d'identité de l'équipement -->
    <div class="info-card">
        <h3 style="margin-top: 0;">Informations Techniques</h3>
        <table>
            <tr>
                <th width="25%">Numéro d'inventaire</th>
                <td>{{ $equipement->num_inventaire_eq ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Numéro de série</th>
                <td>{{ $equipement->num_serie_eq ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Catégorie</th>
                <td>{{ $equipement->categorieEquipement->lib_cat ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Désignation</th>
                <td>{{ $equipement->designation_eq ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Statut</th>
                <td>
                    @if ($equipement->statut_eq == 'disponible')
                        <span class="badge badge-success">Disponible</span>
                    @elseif($equipement->statut_eq == 'attribué')
                        <span class="badge badge-warning">Attribué</span>
                        @if (isset($equipement->user))
                            ({{ $equipement->user->mat_ag ?? 'N/A' }})
                        @endif
                    @else
                        <span class="badge badge-danger">{{ ucfirst($equipement->statut_eq) }}</span>
                    @endif
                </td>
            </tr>
            <tr>
                <th>État technique</th>
                <td>
                    @if ($equipement->etat_eq == 'Bon état')
                        <span class="badge badge-success">Bon état</span>
                    @elseif($equipement->etat_eq == 'En panne')
                        <span class="badge badge-danger">En panne</span>
                    @else
                        {{ $equipement->etat_eq ?? 'N/A' }}
                    @endif
                </td>
            </tr>
            <tr>
                <th>Date d'acquisition</th>
                <td>{{ $equipement->date_acq_formatted }}</td>
            </tr>
        </table>
    </div>

    <!-- Statistiques -->
    <div class="info-card">
        <h3 style="margin-top: 0;">Statistiques des Pannes</h3>

        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-value">{{ $stats['total_pannes'] }}</div>
                <div class="stat-label">TOTAL PANNES</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ $stats['pannes_resolues'] }}</div>
                <div class="stat-label">PANNES RÉSOLUES</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ $stats['taux_resolution'] }}%</div>
                <div class="stat-label">TAUX DE RÉSOLUTION</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ $stats['pannes_en_cours'] }}</div>
                <div class="stat-label">EN COURS</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">{{ $stats['pannes_en_attente'] }}</div>
                <div class="stat-label">EN ATTENTE</div>
            </div>
            {{-- <div class="stat-item">
                <div class="stat-value">{{ $stats['duree_moyenne_reparation'] }}h</div>
                <div class="stat-label">MOYENNE RÉPARATION</div>
            </div> --}}
        </div>

        @if (count($stats['diagnostics_frequents']) > 0)
            <h4>Panne fréquente :</h4>
            @foreach ($stats['diagnostics_frequents'] as $diag => $count)
                <div class="diagnostic-item">
                    <strong>{{ $diag }}</strong> ({{ $count }} occurrence{{ $count > 1 ? 's' : '' }})
                </div>
            @endforeach
        @endif
    </div>

    <!-- Historique complet des pannes -->
    <div class="info-card">
        <h3 style="margin-top: 0;">Historique Complet des Pannes</h3>

        @if (count($panneHistory) > 0)
            <table>
                <thead>
                    <tr>
                        <th width="15%">Date signalement</th>
                        <th width="15%">Dates prise en charge</th>
                        <th width="25%">Dates resolution</th>
                        <th width="20%">Description</th>
                        <th width="15%">Diagnostic</th>
                        {{-- <th width="10%">Urgence</th> --}}
                        <th width="10%">Statut</th>
                        <th width="10%">Technicien</th>

                    </tr>
                </thead>
                <tbody>
                    @foreach ($panneHistory as $panne)
                        <tr>
                            <td>{{ $panne['date_signa'] }}</td>
                            <td> {{ $panne['date_traitement'] }} </td>
                            <td> {{ $panne['date_resolution'] }}</td>
                            <td>{{ $panne['description'] }}</td>
                            <td>{{ $panne['diagnostic'] }}</td>
                            {{-- <td>
                                @if ($panne['urgence'] == 'Haute')
                                    <span class="badge badge-danger">Haute</span>
                                @elseif($panne['urgence'] == 'Moyenne')
                                    <span class="badge badge-warning">Moyenne</span>
                                @else
                                    <span class="badge badge-info">Basse</span>
                                @endif
                            </td> --}}
                            <td>
                                @if ($panne['status'] == 'Résolue')
                                    <span class="badge badge-success">Résolue</span>
                                @elseif($panne['status'] == 'En cours')
                                    <span class="badge badge-warning">En cours</span>
                                @elseif($panne['status'] == 'En attente')
                                    <span class="badge badge-secondary">En cours</span>
                                @else
                                    <span class="badge badge-danger">Annulée</span>
                                @endif
                            </td>
                            <td>{{ $panne['technicien'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>Aucune panne enregistrée pour cet équipement.</p>
        @endif
    </div>

    <!-- Pied de page -->
    <div style="text-align: right; font-size: 10px; color: #7f8c8d; margin-top: 30px;">
        Document généré le {{ $report_date }} - Page 1/1
    </div>
</body>

</html>
