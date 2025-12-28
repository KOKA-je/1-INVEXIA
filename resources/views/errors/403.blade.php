<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>403 - Accès refusé</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f2f4f7;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
        }

        .error-container {
            text-align: center;
            max-width: 600px;
            padding: 2rem;
        }

        .error-svg {
            width: 150px;
            margin-bottom: 1.5rem;
        }

        .error-code {
            font-size: 4rem;
            font-weight: bold;
            color: #dc3545;
        }

        .error-message {
            font-size: 1.3rem;
            color: #495057;
            margin-bottom: 1rem;
        }

        .btn-home {
            margin-top: 1.5rem;
        }
    </style>
</head>

<body>
    <div class="error-container">
        {{-- SVG moderne --}}
        <svg class="error-svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#dc3545"
            xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M12 9v2.25m0 4.5h.008M21.75 12a9.75 9.75 0 11-19.5 0 9.75 9.75 0 0119.5 0z" />
        </svg>

        <div class="error-code">403</div>
        <div class="error-message">Accès refusé</div>
        <p>Vous n’avez pas les autorisations nécessaires pour consulter cette page.</p>
    </div>
</body>

</html>
