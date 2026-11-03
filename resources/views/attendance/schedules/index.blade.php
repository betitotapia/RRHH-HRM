@extends('layouts.admin')
@section('title','Horarios laborales')
@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<div class="card"><div class="card-header"><h3 class="card-title">Horarios por colaborador</h3></div><div class="card-body table-responsive"><table class="table table-striped"><thead><tr><th>Empleado</th><th>Departamento</th><th>Días configurados</th><th></th></tr></thead><tbody>@foreach($employees as $employee)<tr><td>#{{ $employee->employee_number }} - {{ $employee->last_name_1 }} {{ $employee->first_names }}</td><td>{{ $employee->departament?->name }}</td><td>{{ $employee->schedules->count() }}</td><td><a href="{{ route('attendance-schedules.edit',$employee) }}" class="btn btn-primary btn-sm">Configurar semana</a></td></tr>@endforeach</tbody></table>{{ $employees->links() }}</div></div>
@endsection
