<aside id="sidebar" class="sidebar">

    <ul class="sidebar-nav" id="sidebar-nav">

        @if (Auth::user()->hasRole('Super Admin'))
            @php
                $currentRoute = Route::currentRouteName();
            @endphp

            <!-- Dashboard -->
            <li class="nav-item">
                <a class="nav-link dashboard-link" href="{{ route('dashboard') }}" id="dashboard-link">
                    <i class="bi bi-grid"></i>
                    <span>Dashboard</span>
                </a>
            </li>



            <!-- 📌 Gestion des Périphériques -->
            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-toggle="collapse" data-bs-target="#equipements-nav"
                    aria-expanded="false">
                    <i class="bi bi-usb"></i><span>Gestion des équipements</span>
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="equipements-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                    <li><a href="{{ route('equipements.index') }}"><i class="bi bi-circle"></i><span>
                                Equipements</span></a></li>
                    <li>
                        <a href="{{ route('categories.index') }}"
                            class="{{ $currentRoute == 'categories.index' ? 'active' : '' }}">
                            <i class="bi bi-circle"></i><span>Catégorie d'équipement</span>
                        </a>
                    </li>



                    {{-- <li><a href="{{ route('equipements.index') }}"><i
                                class="bi bi-circle"></i><span>Péri</span></a></li> --}}
                </ul>
            </li>



            <!-- 📌 Gestion des Attributions -->
            <!-- Gestion des Attributions -->
            @php
                $attributionRoutes = ['attributions.index', 'attributions.logs'];
                $isAttributionOpen = in_array($currentRoute, $attributionRoutes);
            @endphp
            <li class="nav-item">
                <a class="nav-link {{ $isAttributionOpen ? '' : 'collapsed' }}" data-bs-toggle="collapse"
                    data-bs-target="#attribution-nav" aria-expanded="{{ $isAttributionOpen ? 'true' : 'false' }}">
                    <i class="bi bi-clipboard-check"></i><span>Gestion des attributions</span>
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="attribution-nav" class="nav-content collapse {{ $isAttributionOpen ? 'show' : '' }}"
                    data-bs-parent="#sidebar-nav">
                    <li>
                        <a href="{{ route('attributions.index') }}"
                            class="{{ $currentRoute == 'attributions.index' ? 'active' : '' }}">
                            <i class="bi bi-circle"></i><span>Attributions</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('attributions.logs') }}"
                            class="{{ $currentRoute == 'attributions.logs' ? 'active' : '' }}">
                            <i class="bi bi-circle"></i><span>Historique d'attribution</span>
                        </a>
                    </li>
                </ul>
            </li>

            <!-- 📌 Gestion des accès et rôles -->
            <li class="nav-item">
                <a class="nav-link collapsed" data-bs-toggle="collapse" data-bs-target="#roles-nav"
                    aria-expanded="false">
                    <i class="bi bi-sliders"></i><span>Gestion des accès et rôles</span>
                    <i class="bi bi-chevron-down ms-auto"></i>
                </a>
                <ul id="roles-nav" class="nav-content collapse" data-bs-parent="#sidebar-nav">
                    <li><a href="{{ route('roles.index') }}"><i class="bi bi-circle"></i><span>Rôles</span></a></li>
                    <li><a href="{{ route('permissions.index') }}"><i
                                class="bi bi-circle"></i><span>Permissions</span></a></li>
                    <li><a href="{{ route('users.index') }}"><i class="bi bi-circle"></i><span>Utilisateurs</span></a>
                    </li>
                </ul>
            </li>

            <!-- Pannes link -->
            <li class="nav-item">
                <a class="nav-link panne-link" href="{{ route('pannes.index') }}" id="panne-link">
                    <i class="bi bi-tools"></i>
                    <span>Gestion des pannes</span>
                </a>
            </li>
            {{-- <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}"
                    href="{{ route('notifications.index') }}">
                    <i class="bi bi-bell-fill"></i>
                    <span>Notifications
                        @if (auth()->user()->unreadNotifications->count() > 0)
                            <span class="badge bg-danger">{{ auth()->user()->unreadNotifications->count() }}</span>
                        @endif
                    </span>
                </a>
            </li> --}}


            <li class="nav-item">
                <a class="nav-link" href="{{ route('mes.equipements.stats') }}"> <i
                        class="bi bi-body-text"></i><span>Mon dashboard
                    </span></a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('mon.espace') }}"> <i class="bi bi-person-video3"></i><span>Mes
                        equipements</span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}"
                    href="{{ route('notifications.index') }}">
                    <i class="bi bi-bell-fill"></i>
                    <span>
                        Notifications
                        <span id="unread-notifications-count"
                            class="badge bg-danger {{ auth()->user()->unreadNotifications->count() > 0 ? '' : 'd-none' }}">
                            {{ auth()->user()->unreadNotifications->count() }}
                        </span>
                    </span>
                </a>
            </li>
        @else
            <li class="nav-item">
                <a class="nav-link" href="{{ route('mes.equipements.stats') }}"> <i
                        class="bi bi-body-text"></i><span>Mon dashboard
                    </span></a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('mon.espace') }}"><i class="bi bi-person-video3"></i><span>Mes
                        équipements
                    </span></a>
            </li>


            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('notifications.*') ? 'active' : '' }}"
                    href="{{ route('notifications.index') }}">
                    <i class="bi bi-bell-fill"></i>
                    <span>
                        Notifications
                        <span id="unread-notifications-count"
                            class="badge bg-danger {{ auth()->user()->unreadNotifications->count() > 0 ? '' : 'd-none' }}">
                            {{ auth()->user()->unreadNotifications->count() }}
                        </span>
                    </span>
                </a>
            </li>
        @endif

    </ul>
</aside>
