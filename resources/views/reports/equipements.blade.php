<!DOCTYPE html>
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
        }

        /* Nouveau style pour l'en-tête */
        .header {
            position: relative;
            margin-bottom: 25px;
            padding-top: 10px;
        }

        .company-logo {
            position: absolute;
            left: 0;
            top: 0;
            height: 60px;
            max-width: 200px;
            /* Ajustez selon votre logo */
        }

        .report-title {
            margin-left: 220px;
            /* Ajustez en fonction de la largeur du logo */
            padding-top: 15px;
            text-align: left;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
        }

        .date {
            font-size: 12px;
            color: #666;
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

        .summary-card {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .kpi {
            display: inline-block;
            width: 30%;
            text-align: center;
            margin: 5px;
        }
    </style>
</head>

<body>


    <!-- Nouvel en-tête avec logo -->
    <div class="header">
        <img class="company-logo" src="data:image/png;base64,{{ $logo }}" alt="Logo Entreprise">
        <div class="report-title">
            <h2 style="margin: 0; font-size: 18px;">{{ $title }}</h2>
            <small style="color: #666;">Généré le {{ $date }}</small>
        </div>
    </div>
    <br>

    <div class="summary-card">
        <h3>Statistiques Globales</h3>
        <div>
            <div class="kpi">
                <strong>Total Équipements</strong><br>
                {{ $globalStats['total'] }}
            </div>
            <div class="kpi">
                <strong>Disponibles</strong><br>
                {{ $globalStats['disponibles'] }}
            </div>
            <div class="kpi">
                <strong>En Panne</strong><br>
                {{ $globalStats['en_panne'] }} ({{ $globalStats['pourcentagePanne'] }}%)
            </div>
        </div>
    </div>

    <h3>Détails par Catégorie</h3>
    <table>
        <thead>
            <tr>
                <th>Catégorie</th>
                <th>Total</th>
                <th>Disponibles</th>
                <th>Attribués</th>
                <th>En Panne</th>
                <th>Réformés</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($equipementsStats as $stat)
                <tr>
                    <td>{{ $stat->lib_cat }}</td>
                    <td>{{ $stat->total }}</td>
                    <td>{{ $stat->disponibles }}</td>
                    <td>{{ $stat->attribues }}</td>
                    <td>{{ $stat->en_panne }}</td>
                    <td>{{ $stat->reformes }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
