<nav class="main-header navbar navbar-expand navbar-white navbar-light rh-navbar">
    <ul class="navbar-nav align-items-center">
        <li class="nav-item"><a class="nav-link rh-menu-toggle" data-widget="pushmenu" href="#" role="button" aria-label="Mostrar u ocultar menú"><i class="fas fa-bars"></i></a></li>
        <li class="nav-item d-none d-sm-block"><a href="{{ route('directory.index') }}" class="nav-link rh-top-link"><i class="fas fa-address-book mr-1"></i> Directorio</a></li>
    </ul>
    <ul class="navbar-nav ml-auto align-items-center">
        @auth
            <li class="nav-item dropdown">
                <a class="nav-link rh-user-trigger" data-toggle="dropdown" href="#" aria-expanded="false">
                    <span class="rh-avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                    <span class="rh-user-copy d-none d-md-flex"><strong>{{ auth()->user()->name }}</strong><small>{{ ucfirst(str_replace('_', ' ', auth()->user()->role ?? 'usuario')) }}</small></span>
                    <i class="fas fa-chevron-down rh-user-chevron"></i>
                </a>
                <div class="dropdown-menu dropdown-menu-right rh-user-menu">
                    <div class="rh-user-menu-header"><span class="rh-avatar rh-avatar-lg">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span><div><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->email }}</small></div></div>
                    <div class="dropdown-divider"></div>
                    <a href="{{ route('profile.edit') }}" class="dropdown-item"><i class="fas fa-user-circle mr-2 text-primary"></i> Mi perfil</a>
                    <form method="POST" action="{{ route('logout') }}">@csrf<button type="submit" class="dropdown-item text-danger"><i class="fas fa-sign-out-alt mr-2"></i> Cerrar sesión</button></form>
                </div>
            </li>
        @endauth
    </ul>
</nav>
