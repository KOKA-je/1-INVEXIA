<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Rapport des équipements' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .title {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .date {
            font-size: 14px;
            color: #666;
        }

        .section-title {
            font-size: 18px;
            font-weight: bold;
            margin-top: 25px;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 1px solid #ccc;
        }

        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .info-table td {
            padding: 8px;
            border: 1px solid #eee;
            vertical-align: top;
        }

        .info-table .label {
            font-weight: bold;
            width: 30%;
            /* Adjust as needed */
            background-color: #f9f9f9;
        }

        .panne-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 12px;
            /* Smaller font for table to fit more data */
        }

        .panne-table th,
        .panne-table td {
            border: 1px solid #ccc;
            padding: 6px;
            /* Reduced padding */
            text-align: left;
        }

        .panne-table th {
            background-color: #e9ecef;
        }

        ul.diagnostic-list {
            list-style-type: none;
            padding-left: 0;
            margin-bottom: 15px;
        }

        ul.diagnostic-list li {
            padding: 3px 0;
            line-height: 1.4;
        }

        .footer {
            margin-top: 40px;
            font-size: 12px;
            text-align: center;
            color: #666;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>
    <div class="header">
        @if (isset($logo) && $logo)
            <img src="{{ $logo }}" alt="Logo" style="max-width: 120px; height: auto; margin-bottom: 10px;">
        @endif
        <div class="title">{{ $title ?? 'Rapport Détaillé Équipement' }}</div>
        <div class="date">Généré le {{ $date ?? now()->format('d/m/Y H:i') }}</div>
    </div>

    <div class="section-title">Informations sur l'équipement</div>
    <table class="info-table">
        <tr>
            <td class="label">Nom de l'équipement:</td>
            <td>{{ $equipement->nom_eq ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Numéro d'inventaire:</td>
            <td>{{ $equipement->num_inventaire_eq ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Description:</td>
            <td>{{ $equipement->description_eq ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Catégorie:</td>
            <td>{{ $equipement->categorieEquipement->lib_cat ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Date d'acquisition:</td>
            <td>{{ $equipement->date_acq ? $equipement->date_acq->format('d/m/Y') : 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">État actuel:</td>
            <td>{{ $equipement->etat_eq ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Statut:</td>
            <td>{{ $equipement->statut_eq ?? 'N/A' }}</td>
        </tr>
        <tr>
            <td class="label">Propriétaire:</td>
            <td>{{ $equipement->user->name ?? 'N/A' }}</td>
        </tr>
    </table>

    <div class="section-title">Statistiques des pannes</div>
    <table class="info-table">
        <tr>
            <td class="label">Total des pannes enregistrées:</td>
            <td>{{ $stats['total_pannes'] ?? 0 }}</td>
        </tr>
        <tr>
            <td class="label">Pannes résolues:</td>
            <td>{{ $stats['pannes_resolues'] ?? 0 }}</td>
        </tr>
        <tr>
            <td class="label">Durée moyenne de réparation (heures):</td>
            <td>{{ isset($stats['duree_moyenne_reparation']) ? number_format($stats['duree_moyenne_reparation'], 2) : 'N/A' }}
            </td>
        </tr>
    </table>

    ---
    <div class="section-title">Diagnostics les plus fréquents</div>
    @if (!empty($stats['diagnostics_counts']))
        <ul class="diagnostic-list">
            @foreach ($stats['diagnostics_counts'] as $diagnostic => $count)
                <li><strong>{{ $diagnostic }}</strong>: {{ $count }} fois</li>
            @endforeach
        </ul>
    @else
        <p>Aucun diagnostic de panne enregistré pour cet équipement.</p>
    @endif
    ---

    <div class="section-title">Historique des 10 dernières pannes</div>
    @if (isset($equipement->pannes) && $equipement->pannes->isNotEmpty())
        <table class="panne-table">
            <thead>
                <tr>
                    <th>Date Signalement</th>
                    <th>Statut</th>
                    <th>Diagnostic</th>
                    <th>Action</th>
                    <th>Date Résolution</th>
                    <th>Traité par</th>
                </tr>
            </thead>
            <tbody>
                {{-- Ensure pannes are ordered by latest first, and limit to 10 for display --}}
                @foreach ($equipement->pannes->sortByDesc('created_at')->take(10) as $panne)
                    <tr>
                        <td>{{ $panne->date_signa ? $panne->date_signa->format('d/m/Y') : 'N/A' }}</td>
                        <td>{{ $panne->sta_pan ?? 'N/A' }}</td>
                        <td>{{ $panne->diag_pan ?? 'N/A' }}</td>
                        <td>{{ $panne->action_pan ?? 'N/A' }}</td>
                        <td>{{ $panne->date_rsl ? $panne->date_rsl->format('d/m/Y') : 'N/A' }}</td>
                        <td>{{ $panne->user2->mat_ag ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p>Aucun historique de panne pour cet équipement.</p>
    @endif

    <div class="footer">
        Rapport généré automatiquement par le système de gestion des équipements
    </div>
</body>

</html>
