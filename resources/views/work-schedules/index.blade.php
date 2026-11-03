@extends('layouts.admin')
@section('title', 'Horarios laborales')
@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif
<div class="card card-primary card-outline">
 <div class="card-header"><h3 class="card-title">Catálogo de horarios</h3><div class="card-tools"><a href="{{ route('attendance-schedules.create') }}" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Nuevo horario</a></div></div>
 <div class="card-body table-responsive p-0"><table class="table table-striped table-hover mb-0">
  <thead><tr><th>Nombre</th><th>Clave</th><th>Descansos</th><th>Días laborables</th><th>Empleados</th><th>Estado</th><th style="width:170px">Acciones</th></tr></thead>
  <tbody>@forelse($workSchedules as $schedule)<tr><td>{{ $schedule->name }}</td><td>{{ $schedule->code }}</td><td>{{ $schedule->has_breakfast ? 'Desayuno' : '' }}{{ $schedule->has_breakfast && $schedule->has_lunch ? ' y ' : '' }}{{ $schedule->has_lunch ? 'Comida' : '' }}{{ !$schedule->has_breakfast && !$schedule->has_lunch ? 'Ninguno' : '' }}</td><td>{{ $schedule->days_count }}</td><td>{{ $schedule->employees_count }}</td><td><span class="badge badge-{{ $schedule->active ? 'success' : 'secondary' }}">{{ $schedule->active ? 'Activo' : 'Inactivo' }}</span></td><td class="text-nowrap"><a href="{{ route('attendance-schedules.edit',$schedule) }}" class="btn btn-info btn-sm">Editar</a> <form action="{{ route('attendance-schedules.destroy',$schedule) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este horario?')">@csrf @method('DELETE')<button class="btn btn-danger btn-sm">Eliminar</button></form></td></tr>@empty<tr><td colspan="7" class="text-center py-4">Aún no hay horarios registrados.</td></tr>@endforelse</tbody>
 </table></div>
 @if($workSchedules->hasPages())<div class="card-footer">{{ $workSchedules->links() }}</div>@endif
</div>
@endsection
