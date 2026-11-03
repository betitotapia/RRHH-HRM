@extends('layouts.admin')
@section('title', 'Nuevo usuario')
@section('content')
<div class="card card-primary"><div class="card-header"><h3 class="card-title">Crear acceso al sistema</h3></div>
<form method="POST" action="{{ route('users.store') }}">@csrf<div class="card-body">@include('users._form')</div><div class="card-footer"><button class="btn btn-primary"><i class="fas fa-user-plus mr-1"></i> Crear usuario</button> <a href="{{ route('users.index') }}" class="btn btn-secondary">Cancelar</a></div></form></div>
@endsection
