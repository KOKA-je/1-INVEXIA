<header id="header" class="header fixed-top d-flex align-items-center">

    <div class="mb-1 d-flex align-items-center justify-content-between">
        <a href="#" class="logo d-flex align-items-center" with="90px" height="90px">
            <img src="{{ asset('img/v.png') }}" alt="">
            <span class="mt-2 d-none d-lg-block">Invexia</span>
        </a>
    </div>
    <i class="mt-2 mr-2 bi bi-list toggle-sidebar-btn"></i><!-- End Logo -->
    <!-- End Search Bar -->
    <nav class="header-nav ms-auto">
        <ul class="gap-3 d-flex align-items-center me-3">
            <li class="nav-item dropdown">
                <a class="gap-2 mr-2 nav-link nav-profile d-flex align-items-center pe-0" href="#"
                    data-bs-toggle="dropdown" aria-expanded="true">
                    <div class="avatar avatar-sm">
                        <div
                            class="text-white avatar-initials bg-primary rounded-circle d-flex align-items-center justify-content-center">
                            {{ substr(Auth::user()->pren_ag, 0, 1) }}{{ substr(Auth::user()->nom_ag, 0, 1) }}
                        </div>
                    </div>
                    <div class="profile-info d-none d-md-block text-start">
                        <span class="fw-semibold d-block">{{ Auth::user()->mat_ag }}</span>
                        <small class="text-muted">
                            {{ Auth::user()->dir_ag }}
                            @if (Auth::user()->loc_ag)
                                • {{ substr(Auth::user()->loc_ag, 0, 20) }}
                                {{-- affiche que les 10 premiers caractères --}}
                            @endif
                        </small>
                    </div>
                    <i class="bi bi-chevron-down dropdown-indicator"></i>
                </a>

                <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile animated fadeIn">
                    <li class="text-center dropdown-header">
                        <div class="mb-2 avatar avatar-lg">
                            <div
                                class="text-white avatar-initials bg-primary rounded-circle d-flex align-items-center justify-content-center">
                                {{ substr(Auth::user()->pren_ag, 0, 1) }}{{ substr(Auth::user()->nom_ag, 0, 1) }}
                            </div>
                        </div>
                        <h6 class="mb-1">
                            {{ substr(Auth::user()->pren_ag, 0, 20) }} {{ Auth::user()->nom_ag }}</h6>
                        <p class="mb-0 text-muted small">{{ Auth::user()->mat_ag }}</p>
                        <p class="text-muted small">
                            {{ Auth::user()->dir_ag }}
                            @if (Auth::user()->loc_ag)
                                • {{ substr(Auth::user()->loc_ag, 0, 20) }}
                            @endif
                        </p>
                    </li>

                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    <li>
                        <a class="dropdown-item d-flex align-items-center text-danger" href="{{ route('logout') }}"
                            onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            <i class="bi bi-box-arrow-right me-2"></i>
                            <span>Déconnexion</span>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                style="display: none;">
                                @csrf
                            </form>
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>

    <style>
        /* Custom Styles */
        .avatar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .avatar-sm .avatar-initials {
            width: 32px;
            height: 32px;
            font-size: 0.75rem;
        }

        .avatar-lg .avatar-initials {
            width: 64px;
            height: 64px;
            font-size: 1.5rem;
        }

        .profile-info {
            line-height: 1.2;
        }

        .dropdown-menu.profile {
            width: 280px;
        }

        .dropdown-menu.notifications {
            width: 320px;
            max-height: 400px;
            overflow-y: auto;
        }

        .dropdown-header h6 {
            font-weight: 600;
        }

        .nav-icon {
            padding: 0.5rem;
            transition: all 0.2s;
        }

        .nav-icon:hover {
            transform: translateY(-2px);
        }

        .animated {
            animation-duration: 0.3s;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fadeIn {
            animation-name: fadeIn;
        }
    </style>
</header><!-- End Header -->
