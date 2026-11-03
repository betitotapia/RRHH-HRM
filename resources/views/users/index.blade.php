@extends('layouts.admin')
@section('title', 'Usuarios')
@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
<div class="card card-outline card-primary">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
        <h3 class="card-title">Accesos al sistema</h3>
        <div class="card-tools d-flex mt-2 mt-md-0">
            <form method="GET" class="mr-2"><div class="input-group input-group-sm"><input name="search" class="form-control" placeholder="Nombre o correo" value="{{ $search }}"><div class="input-group-append"><button class="btn btn-default"><i class="fas fa-search"></i></button></div></div></form>
            <a href="{{ route('users.create') }}" class="btn btn-sm btn-primary"><i class="fas fa-user-plus"></i> Nuevo usuario</a>
        </div>
    </div>
    <div class="card-body p-0 table-responsive"><table class="table table-hover mb-0"><thead><tr><th>Usuario</th><th>Rol</th><th>Estado</th><th>Último acceso</th><th>Acciones</th></tr></thead><tbody>
    @forelse($users as $user)<tr>
        <td><strong>{{ $user->name }}</strong><br><small class="text-muted">{{ $user->email }}</small></td>
        <td><span class="badge badge-info">{{ $roles[$user->role] ?? $user->role }}</span></td>
        <td><span class="badge badge-{{ $user->is_active ? 'success' : 'secondary' }}">{{ $user->is_active ? 'Activo' : 'Inactivo' }}</span></td>
        <td>{{ $user->last_login_at?->format('d/m/Y H:i') ?? 'Nunca' }}</td>
        <td class="text-nowrap">
            <a href="{{ route('users.edit',$user) }}" class="btn btn-sm btn-info" title="Editar"><i class="fas fa-edit"></i></a>
            <form class="d-inline" method="POST" action="{{ route('users.toggle-status',$user) }}">@csrf @method('PATCH')<button class="btn btn-sm btn-{{ $user->is_active ? 'warning' : 'success' }}" title="{{ $user->is_active ? 'Desactivar' : 'Activar' }}" @disabled(auth()->id()===$user->id && $user->is_active)><i class="fas fa-{{ $user->is_active ? 'ban' : 'check' }}"></i></button></form>
            <form class="d-inline" method="POST" action="{{ route('users.reset-link',$user) }}">@csrf<button class="btn btn-sm btn-outline-primary" title="Enviar enlace para restablecer contraseña"><i class="fas fa-key"></i></button></form>
        </td>
    </tr>@empty<tr><td colspan="5" class="text-center py-4">No hay usuarios registrados.</td></tr>@endforelse
    </tbody></table></div>
    @if($users->hasPages())<div class="card-footer">{{ $users->links() }}</div>@endif
</div>
@endsection
