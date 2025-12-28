<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>404 - Page non trouvée</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
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
            width: 160px;
            margin-bottom: 1.5rem;
        }

        .error-code {
            font-size: 4rem;
            font-weight: bold;
            color: #0d6efd;
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
        {{-- SVG "File Not Found" stylisé --}}
        <svg class="error-svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="#0d6efd"
            xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M9 12h6m2 7H7a2 2 0 01-2-2V7a2 2 0 012-2h7.586a1 1 0 01.707.293l3.414 3.414A1 1 0 0119 9.414V17a2 2 0 01-2 2z" />
        </svg>

        <div class="error-code">404</div>
        <div class="error-message">Page introuvable</div>
        <p>La page que vous cherchez n’existe pas ou a été déplacée.</p>

    </div>
</body>

</html>
