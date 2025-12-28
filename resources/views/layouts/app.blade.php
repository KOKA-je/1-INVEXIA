<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title')</title>
    <meta content="" name="description">
    <meta content="" name="keywords">
    <!-- Favicons -->
    <link href="{{ asset('assets/img/favikof.png') }}" rel="icon">
    <link href="{{ asset('assets/img/apple-touch-icon.png') }}" rel="apple-touch-icon">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.gstatic.com" rel="preconnect">
    <link
        href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i"
        rel="stylesheet">
    <!-- Vendor CSS Files -->
    <link href="{{ asset('assets/vendor/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/bootstrap-icons/bootstrap-icons.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/boxicons/css/boxicons.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/quill/quill.snow.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/quill/quill.bubble.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/remixicon/remixicon.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/vendor/simple-datatables/style.css') }}" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous">
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- son notif --}}
    <link rel="preload" href="{{ asset('sounds/notifications.mp3') }}" as="audio">

    <script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
    {{-- Script pour notification Nouvelle Attribution  --}}


    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>



    @yield('style')



    <!-- Template Main CSS File -->
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">

    {{-- lien select 2 --}}

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    {{-- @vite('resources/css/app.css', 'resources/js/app.js') --}}

    <!-- Template Main CSS File -->
    <link href="assets/css/style.css" rel="stylesheet">

    <!-- =======================================================
  * Template Name: NiceAdmin
  * Template URL: https://bootstrapmade.com/nice-admin-bootstrap-admin-html-template/
  * Updated: Apr 20 2024 with Bootstrap v5.3.3
  * Author: BootstrapMade.com
  * License: https://bootstrapmade.com/license/
  ======================================================== -->

</head>

<body>

    <div class="loader-overlay" id="loader">
        <div class="loader-spinner"></div>
    </div>

    <!-- ======= Header ======= -->

    @include('layouts.partials.header')


    <!-- ======= Sidebar ======= -->

    @include('layouts.partials.Sidebar')


    <main id="main" class="main">



        <section class="section">

            @include('layouts.partials.header-content')

            <br>

            @include('layouts.partials.message')

            @yield('voidgrubs')

            @yield('content')


        </section>

    </main><!-- End #main -->

    <!-- ======= Footer ======= -->

    @include('layouts.partials.footer')


    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i
            class="bi bi-arrow-up-short"></i></a>

    <!-- Vendor JS Files -->

    <script src="{{ asset('assets/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/chart.js/chart.umd.js') }}"></script>
    <script src="{{ asset('assets/vendor/echarts/echarts.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/quill/quill.js') }}"></script>
    <script src="{{ asset('assets/vendor/simple-datatables/simple-datatables.js') }}"></script>
    <script src="{{ asset('assets/vendor/tinymce/tinymce.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/php-email-form/validate.js') }}"></script>
    <script src="{{ asset('js/app.js') }}"></script>

    <!-- Template Main JS File -->
    <script src="assets/js/main.js"></script>


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script src="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css"></script>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    {{-- js pour datatable --}}

    @yield('scripts')

    {{-- script pour select 2 --}}

    @yield('select')

    <script>
        $(document).ready(function() {
            $('.js-example-basic-multiple').select2({
                placeholder: "Sélectionnez un ou plusieurs équipements",
                allowClear: true
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>


    {{-- SCRIPT POUR LE TOAST --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var toastElList = [].slice.call(document.querySelectorAll('.toast'))
            var toastList = toastElList.map(function(toastEl) {
                return new bootstrap.Toast(toastEl, {
                    autohide: true,
                    delay: 5000
                }) // 5 secondes
            })
            toastList.forEach(toast => toast.show());
        });
    </script>

    {{-- Code pour la sidebar active --}}
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const currentUrl = window.location.href;
            const allNavLinks = document.querySelectorAll('.sidebar-nav a.nav-link');
            const dashboardLink = document.getElementById('dashboard-link');

            // Désactiver tous les liens d'abord
            allNavLinks.forEach(link => {
                link.classList.remove('active');
                link.classList.add('collapsed');
            });

            // Activer seulement le lien correspondant
            allNavLinks.forEach(link => {
                const linkUrl = link.href;

                // Vérifier si l'URL actuelle correspond (sans les paramètres)
                if (currentUrl.split('?')[0] === linkUrl.split('?')[0]) {
                    link.classList.add('active');
                    link.classList.remove('collapsed');

                    // Gérer l'ouverture des sous-menus
                    const parentMenu = link.closest('.nav-content');
                    if (parentMenu) {
                        parentMenu.classList.add('show');
                        const parentToggle = document.querySelector(`[data-bs-target="#${parentMenu.id}"]`);
                        if (parentToggle) {
                            parentToggle.classList.remove('collapsed');
                            parentToggle.setAttribute('aria-expanded', 'true');
                        }
                    }
                }
            });

            // Cas spécial pour le Dashboard
            // if (!currentUrl.includes('dashboard')) {
            //     dashboardLink.classList.remove('active');
            //     dashboardLink.classList.add('collapsed');
            // }
        });
    </script>




    {{-- script pour mon loader --}}

    <script>
        window.addEventListener('load', function() {
            const loader = document.getElementById('loader');
            if (loader) {
                loader.style.display = 'none';
            }
        });
    </script>





    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 1. Get CSRF token from meta tag
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

            // 2. Initialize Pusher with robust configuration
            const pusher = new Pusher('{{ config('broadcasting.connections.pusher.key') }}', {
                cluster: '{{ config('broadcasting.connections.pusher.options.cluster') }}',
                authEndpoint: '/broadcasting/auth',
                forceTLS: true,
                enabledTransports: ['ws', 'wss'],
                auth: {
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                }
            });

            // 3. Enhanced error handling
            // pusher.connection.bind('error', (err) => {
            //     console.error('Pusher connection error:', err);
            //     showSystemToast('Connection error', 'Failed to connect to real-time service', 'danger');
            // });

            // pusher.connection.bind('state_change', (states) => {
            //     console.log('Pusher connection state changed:', states);
            //     if (states.current === 'failed') {
            //         showSystemToast('Connection lost', 'Attempting to reconnect...', 'warning');
            //     }
            //     if (states.current === 'connected') {
            //         showSystemToast('Connected', 'Real-time updates active', 'success', 3000);
            //     }
            // });

            // 4. Toast container management (unchanged)
            function getToastContainer() {
                let container = document.getElementById('toast-global-container');
                if (!container) {
                    container = document.createElement('div');
                    container.id = 'toast-global-container';
                    container.className = 'toast-container position-fixed top-0 end-0 p-3';
                    container.style.zIndex = '1060';
                    document.body.appendChild(container);
                }
                return container;
            }

            // 5. Enhanced toast function
            function showToast(data, options = {}) {
                const container = getToastContainer();
                const toastId = 'toast-' + Date.now();

                // Default configuration
                const config = {
                    type: 'info',
                    icon: 'bi-info-circle',
                    bgClass: 'bg-primary',
                    delay: 5000,
                    autoHide: true

                };

                // Handle different notification structures
                const notification = data.notification || data;
                const timestamp = notification.timestamp ? new Date(notification.timestamp) : new Date();

                const toastHTML = `
        <div id="${toastId}" class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header ${config.bgClass} text-white">
                <i class="bi ${config.icon} me-2"></i>
                <strong class="me-auto">${notification.type || 'Notification'}</strong>
                <small>${timestamp.toLocaleTimeString()}</small>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                ${notification.message ? `<p><strong>${notification.message}</strong></p>` : ''}
                ${notification.equipements ? `
                                                                                                                                                                <p>Équipement(s):</p>
                                                                                                                                                                <ul class="mb-1">
                                                                                                                                                                    ${notification.equipements.map(eq => `
                            <li>${eq.nom} (${eq.num_inventaire})</li>
                        `).join('')}
                                                                                                                                                                </ul>
                                                                                                                                                            ` : ''}
                ${notification.equipement ? `
                                                                                                                                                                <p>Équipement: <strong>${notification.equipement.nom || notification.equipement.nom_eq} (${notification.equipement.num_inventaire || notification.equipement.num_inventaire_eq})</strong></p>
                                                                                                                                                            ` : ''}
                ${notification.signaled_by ? `<small class="d-block">Signalé par: ${notification.signaled_by}</small>` : ''}
                ${notification.updated_by ? `<small class="d-block">Mis à jour par: ${notification.updated_by}</small>` : ''}
                ${notification.attributed_by ? `<small class="d-block">Attribué par: ${notification.attributed_by}</small>` : ''}
                ${notification.panne_id ? `

                                                                                                                                                            ` : ''}
            </div>
        </div>`;

                container.insertAdjacentHTML('afterbegin', toastHTML);

                const toastElement = document.getElementById(toastId);
                const toast = new bootstrap.Toast(toastElement, {
                    delay: config.delay,
                    autohide: config.delay > 0
                });

                toast.show();

                toastElement.addEventListener('hidden.bs.toast', () => {
                    toastElement.remove();
                });
            }

            // Helper for system toasts
            function showSystemToast(title, message, type = 'info', delay = 5000) {
                showToast({
                    type: title,
                    message: message
                }, {
                    type: type,
                    icon: type === 'danger' ? 'bi-exclamation-octagon' : type === 'warning' ?
                        'bi-exclamation-triangle' : type === 'success' ? 'bi-check-circle' :
                        'bi-info-circle',
                    bgClass: `bg-${type}` + (type === 'warning' ? ' text-dark' : ' text-white'),
                    delay: delay
                });
            }

            // 6. Notification badge management
            function updateNotificationBadge() {
                const badge = document.getElementById('unread-notifications-count');
                if (badge) {
                    const count = parseInt(badge.textContent) || 0;
                    badge.textContent = count + 1;
                    badge.classList.remove('d-none');
                    try {
                        // Solution recommandée avec le helper asset()
                        new Audio("{{ asset('sounds/notifications.mp3') }}").play();

                        // Alternative si vous utilisez Vite
                        // new Audio("/sounds/notification.mp3").play();
                    } catch (e) {
                        console.error('Erreur de lecture audio:', e);
                    }
                }
            }

            // 7. Channel subscription (only for authenticated users)
            const userId = @json(auth()->id());
            if (userId) {
                const channel = pusher.subscribe('private-user.' + userId);

                // Event handlers
                channel.bind('attribution.created', (data) => {
                    console.log('Attribution created:', data);
                    updateNotificationBadge();
                    showToast(data, {
                        type: 'success',
                        icon: 'bi-box-arrow-in-right',
                        bgClass: 'bg-success',
                        delay: 10000
                    });
                });

                channel.bind('attribution.removed', (data) => {
                    console.log('Attribution removed:', data);
                    updateNotificationBadge();
                    showToast(data, {
                        type: 'danger',
                        icon: 'bi-box-arrow-left',
                        bgClass: 'bg-danger',
                        delay: 10000
                    });
                });

                channel.bind('panne.signaled', (data) => {
                    console.log('Panne reported:', data);
                    updateNotificationBadge();
                    showToast(data, {
                        type: 'warning',
                        icon: 'bi-exclamation-triangle',
                        bgClass: 'bg-warning text-dark',
                        delay: 15000
                    });
                });

                // Add handler for panne.treated event
                channel.bind('panne.treated', (data) => {
                    console.log('Panne treated:', data);
                    updateNotificationBadge();
                    showToast(data, {
                        type: data.notification?.statut === 'Résolue' ? 'success' : data
                            .notification?.statut === 'Rejetée' ? 'danger' : 'info',
                        icon: data.notification?.statut === 'Résolue' ? 'bi-check-circle' : data
                            .notification?.statut === 'Rejetée' ? 'bi-x-circle' : 'bi-info-circle',
                        bgClass: data.notification?.statut === 'Résolue' ? 'bg-success' : data
                            .notification?.statut === 'Rejetée' ? 'bg-danger' : 'bg-primary',
                        delay: 15000
                    });
                });

                // Debug: Log all events
                if (process.env.NODE_ENV === 'development') {
                    channel.bind_global((event, data) => {
                        console.log('Global event:', event, data);
                    });
                }
            }
        });
    </script>










</body>

</html>
