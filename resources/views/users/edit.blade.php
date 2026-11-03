@extends('layouts.admin')
@section('title', 'Editar usuario')
@section('content')
<div class="card card-primary"><div class="card-header"><h3 class="card-title">Acceso y vinculación</h3></div>
<form method="POST" action="{{ route('users.update', $user) }}">@csrf @method('PUT')<div class="card-body">@include('users._form')</div><div class="card-footer"><button class="btn btn-primary"><i class="fas fa-save mr-1"></i> Guardar cambios</button> <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancelar</a></div></form></div>
@endsection
