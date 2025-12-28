<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Connexion | INVEXIA</title>

    {{-- Bootstrap CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- Font Awesome --}}
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    {{-- Google Fonts --}}
    <link
        href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;500;600;700&family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet">

    <style>
        :root {
            --primary-color: #4361ee;
            --secondary-color: #3a0ca3;
            --accent-color: #f72585;
            --light-bg: rgba(255, 255, 255, 0.96);
        }

        body {
            font-family: 'Poppins', sans-serif;
            color: #000;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        .split-container {
            display: flex;
            min-height: 100vh;
        }

        .form-section {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
            background-color: var(--light-bg);
        }

        .logo-section {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: "Rubik", sans-serif;
            background: linear-gradient(-35deg, #ced4f6ff, #d4fae5ff, #cbc8e0ff, #fadaf8f5);
            background-size: 400% 400%;
            animation: gradientBG 5s ease infinite;
            position: relative;
        }

        /* Animation du fond */
        @keyframes gradientBG {
            0% {
                background-position: 0% 50%;
            }

            50% {
                background-position: 100% 50%;
            }

            100% {
                background-position: 0% 50%;
            }
        }

        .login-card {
            max-width: 450px;
            width: 100%;
            padding: 2.5rem;
            animation: fadeIn 0.6s ease-out forwards;
        }

        .logo-container {
            text-align: center;
            z-index: 1;
            padding: 2rem;
            max-width: 80%;
        }

        .login-logo {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            background-image: url('{{ asset('img/cnps.png') }}');
            background-size: cover;
            background-position: center;
            margin: 0 auto 2rem;
            border: 5px solid white;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            transition: transform 0.5s ease;
            background-color: white;
        }

        .login-logo:hover {
            transform: scale(1.05) rotate(5deg);
        }

        .app-title {
            color: var(--secondary-color);
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .app-subtitle {
            color: var(--primary-color);
            font-size: 1.2rem;
            max-width: 400px;
            margin: 0 auto;
            font-weight: 500;
        }

        .title {
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            font-size: 1.75rem;
            position: relative;
            display: inline-block;
        }

        .title::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 50px;
            height: 3px;
            background: var(--accent-color);
            border-radius: 3px;
        }

        .form-control {
            padding: 0.75rem 1rem;
            border-radius: 0.75rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.2);
        }

        .input-group-text {
            background-color: white;
            border-radius: 0.75rem 0 0 0.75rem !important;
            border-right: none;
        }

        .input-group .form-control {
            border-left: none;
            padding-left: 0.5rem;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border: none;
            padding: 0.75rem;
            border-radius: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            font-size: 0.9rem;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(58, 12, 163, 0.3);
        }

        .footer-text {
            color: #6c757d;
            font-size: 0.85rem;
            margin-top: 2rem;
        }

        /* Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Toggle password visibility */
        .password-toggle {
            cursor: pointer;
            background: white;
            border-radius: 0 0.75rem 0.75rem 0 !important;
            border-left: none !important;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .split-container {
                flex-direction: column;
            }

            .logo-section {
                padding: 4rem 2rem;
                min-height: 300px;
            }

            .login-logo {
                width: 120px;
                height: 120px;
            }

            .app-title {
                font-size: 2rem;
            }
        }
    </style>
</head>

<body>
    <div class="split-container">
        {{-- Section Formulaire --}}
        <div class="form-section">
            <div class="login-card">
                <h1 class="title">Connexion</h1>

                {{-- Message d'erreur --}}
                @session('error')
                    <div class="py-2 text-center alert alert-danger alert-dismissible fade show">
                        {{ $value }}
                        {{-- <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button> --}}
                    </div>
                @endsession

                {{-- Formulaire --}}
                <form method="POST" action="{{ route('login') }}" class="needs-validation" novalidate>
                    @csrf

                    {{-- Identifiant --}}
                    <div class="mb-4">
                        <label for="mat_ag" class="form-label fw-medium">Identifiant</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-id-card text-muted"></i></span>
                            <input type="text" class="form-control" id="mat_ag" name="mat_ag"
                                placeholder="exemple : m2498" required>
                        </div>
                        @error('mat_ag')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Mot de passe --}}
                    <div class="mb-4">
                        <label for="yourPassword" class="form-label fw-medium">Mot de passe</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock text-muted"></i></span>
                            <input type="password" class="form-control" id="yourPassword" name="password"
                                placeholder="*******" required autocomplete="current-password">
                            <span class="input-group-text password-toggle" onclick="togglePassword()">
                                <i class="fas fa-eye" id="toggleIcon"></i>
                            </span>
                        </div>
                        @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Remember me option --}}
                    {{-- <div class="mb-4 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label small" for="remember">Se souvenir de moi</label>
                    </div> --}}

                    {{-- Bouton --}}
                    <div class="gap-2 mb-3 d-grid">
                        <button type="submit" class="py-2 btn btn-primary fw-semibold">
                            <i class="fas fa-sign-in-alt me-2"></i>Connexion
                        </button>
                    </div>

                    {{-- Forgot password link --}}
                    {{-- <div class="mb-3 text-center">
                        <a href="#" class="small text-decoration-none" style="color: var(--accent-color);">Mot de
                            passe oublié ?</a>
                    </div> --}}
                </form>

                {{-- Crédit --}}
                <p class="mb-0 text-center footer-text">
                    Koffi Kan Jean-Eudes | Stagiaire CNPS
                </p>
            </div>
        </div>

        {{-- Section Logo --}}
        <div class="logo-section">
            <div class="logo-container">
                <div class="login-logo"></div>
                <h1 class="app-title">INVEXIA</h1>
                {{-- <p class="app-subtitle">Système de gestion intégré de la CNPS</p> --}}
            </div>
        </div>
    </div>

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Toggle password visibility
        function togglePassword() {
            const passwordInput = document.getElementById('yourPassword');
            const toggleIcon = document.getElementById('toggleIcon');

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Form validation
        (function() {
            'use strict'

            const forms = document.querySelectorAll('.needs-validation')

            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault()
                        event.stopPropagation()
                    }

                    form.classList.add('was-validated')
                }, false)
            })
        })()
    </script>
</body>

</html>
