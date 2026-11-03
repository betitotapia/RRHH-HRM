@php
    $userRole = auth()->user()?->role;
    // RoleMiddleware concede acceso global a super_admin; el menú debe reflejarlo.
    $isRhAdmin = in_array($userRole, ['super_admin', 'admin', 'hr'], true);
    $canManageIncidents = in_array($userRole, ['super_admin', 'admin', 'hr', 'supervisor', 'analyst', 'assistant'], true);
    $canManageLeaves = in_array($userRole, ['super_admin', 'admin', 'hr', 'supervisor'], true);
@endphp
<aside class="main-sidebar sidebar-dark-primary elevation-4 rh-sidebar">
    <a href="{{ route('dashboard') }}" class="brand-link rh-brand"><span class="rh-brand-mark"><i class="fas fa-users"></i></span><span class="brand-text"><strong>ERP</strong> RH<small>SUMED</small></span></a>
    <div class="sidebar">
        @auth<div class="rh-sidebar-user"><span class="rh-avatar">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span><div><strong>{{ auth()->user()->name }}</strong><small><i class="fas fa-circle"></i> En línea</small></div></div>@endauth
        <nav class="mt-2 pb-4">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-header">GENERAL</li>
                <li class="nav-item"><a href="{{ route('directory.index') }}" class="nav-link {{ request()->routeIs('directory.*') ? 'active' : '' }}"><i class="nav-icon fas fa-address-book text-info"></i><p>Directorio</p></a></li>
                @if($userRole === 'employee')
                    <li class="nav-item"><a href="{{ route('attendance.my') }}" class="nav-link {{ request()->routeIs('attendance.my') ? 'active' : '' }}"><i class="nav-icon fas fa-fingerprint text-success"></i><p>Mi asistencia</p></a></li>
                @endif
                @if($isRhAdmin)
                    <li class="nav-header">ORGANIZACIÓN</li>
                    <li class="nav-item">
                        <a href="{{ route('employees.index') }}" class="nav-link {{ request()->routeIs('employees.*') ? 'active' : '' }}"><i class="nav-icon fas fa-user-tie text-cyan"></i><p>Colaboradores</p></a>
                    </li>
                    <li class="nav-item {{ request()->routeIs('departaments.*', 'positions.*', 'commissions.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('departaments.*', 'positions.*', 'commissions.*') ? 'active' : '' }}"><i class="nav-icon fas fa-sitemap text-info"></i><p>Estructura organizacional<i class="right fas fa-angle-left"></i></p></a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item"><a href="{{ route('departaments.index') }}" class="nav-link {{ request()->routeIs('departaments.*') ? 'active' : '' }}"><i class="fas fa-building nav-icon"></i><p>Departamentos</p></a></li>
                            <li class="nav-item"><a href="{{ route('positions.index') }}" class="nav-link {{ request()->routeIs('positions.*') ? 'active' : '' }}"><i class="fas fa-id-badge nav-icon"></i><p>Puestos</p></a></li>
                            <li class="nav-item"><a href="{{ route('commissions.index') }}" class="nav-link {{ request()->routeIs('commissions.*') ? 'active' : '' }}"><i class="fas fa-briefcase nav-icon"></i><p>Comisiones</p></a></li>
                        </ul>
                    </li>
                    <li class="nav-header">ASISTENCIA</li>
                    <li class="nav-item {{ request()->routeIs('attendance.admin.*', 'work-locations.*', 'attendance-schedules.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('attendance.admin.*', 'work-locations.*', 'attendance-schedules.*') ? 'active' : '' }}"><i class="nav-icon fas fa-clock text-warning"></i><p>Control de asistencia<i class="right fas fa-angle-left"></i></p></a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item"><a href="{{ route('attendance.admin.index') }}" class="nav-link {{ request()->routeIs('attendance.admin.*') ? 'active' : '' }}"><i class="fas fa-clipboard-check nav-icon"></i><p>Registros</p></a></li>
                            <li class="nav-item"><a href="{{ route('attendance-schedules.index') }}" class="nav-link {{ request()->routeIs('attendance-schedules.*') ? 'active' : '' }}"><i class="fas fa-calendar-alt nav-icon"></i><p>Horarios</p></a></li>
                            <li class="nav-item"><a href="{{ route('work-locations.index') }}" class="nav-link {{ request()->routeIs('work-locations.*') ? 'active' : '' }}"><i class="fas fa-map-marker-alt nav-icon"></i><p>Sedes</p></a></li>
                        </ul>
                    </li>
                @endif
                @if($canManageIncidents || $canManageLeaves)
                    <li class="nav-header">GESTIÓN</li>
                    @if($canManageIncidents)<li class="nav-item"><a href="{{ route('attendance-incidents.index') }}" class="nav-link {{ request()->routeIs('attendance-incidents.*') ? 'active' : '' }}"><i class="nav-icon fas fa-calendar-check text-purple"></i><p>Incidencias</p></a></li>@endif
                    @if($canManageLeaves)<li class="nav-item"><a href="{{ route('leave-requests.index') }}" class="nav-link {{ request()->routeIs('leave-requests.*') ? 'active' : '' }}"><i class="nav-icon fas fa-umbrella-beach text-success"></i><p>Ausencias</p></a></li>@endif
                @endif
                @if($isRhAdmin)
                    <li class="nav-item {{ request()->routeIs('employee-terminations.*', 'termination-types.*') ? 'menu-open' : '' }}">
                        <a href="#" class="nav-link {{ request()->routeIs('employee-terminations.*', 'termination-types.*') ? 'active' : '' }}"><i class="nav-icon fas fa-user-minus text-danger"></i><p>Bajas<i class="right fas fa-angle-left"></i></p></a>
                        <ul class="nav nav-treeview">
                            <li class="nav-item"><a href="{{ route('employee-terminations.index') }}" class="nav-link {{ request()->routeIs('employee-terminations.*') ? 'active' : '' }}"><i class="fas fa-user-slash nav-icon"></i><p>Colaboradores</p></a></li>
                            <li class="nav-item"><a href="{{ route('termination-types.index') }}" class="nav-link {{ request()->routeIs('termination-types.*') ? 'active' : '' }}"><i class="fas fa-list-alt nav-icon"></i><p>Tipos de salida</p></a></li>
                        </ul>
                    </li>
                    <li class="nav-header">ADMINISTRACIÓN</li>
                    <li class="nav-item"><a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}"><i class="nav-icon fas fa-users-cog text-orange"></i><p>Usuarios del sistema</p></a></li>
                @endif
            </ul>
        </nav>
    </div>
</aside>
