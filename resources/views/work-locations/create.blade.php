@extends('layouts.admin')
@section('title','Nueva sede')
@section('content')<div class="card card-primary"><div class="card-header"><h3 class="card-title">Nueva sede</h3></div><form method="POST" action="{{ route('work-locations.store') }}">@csrf<div class="card-body">@include('work-locations._form')</div><div class="card-footer"><button class="btn btn-primary">Guardar</button> <a class="btn btn-secondary" href="{{ route('work-locations.index') }}">Cancelar</a></div></form></div>@endsection
