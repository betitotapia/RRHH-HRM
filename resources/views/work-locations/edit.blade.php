@extends('layouts.admin')
@section('title','Editar sede')
@section('content')<div class="card card-primary"><div class="card-header"><h3 class="card-title">Editar sede</h3></div><form method="POST" action="{{ route('work-locations.update',$workLocation) }}">@csrf @method('PUT')<div class="card-body">@include('work-locations._form')</div><div class="card-footer"><button class="btn btn-primary">Actualizar</button> <a class="btn btn-secondary" href="{{ route('work-locations.index') }}">Cancelar</a></div></form></div>@endsection
