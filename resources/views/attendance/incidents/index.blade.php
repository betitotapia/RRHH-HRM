@extends('layouts.admin')
@section('title','Incidencias')
@section('content')
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
<div class="card"><div class="card-header d-flex justify-content-between"><h3 class="card-title">Incidencias de asistencia</h3><a class="btn btn-primary" href="{{ route('attendance-incidents.create') }}">Nueva incidencia</a></div><div class="card-body table-responsive"><table class="table table-striped"><thead><tr><th>Colaborador</th><th>Tipo</th><th>Inicio</th><th>Fin</th><th>Registró</th><th>Notas</th></tr></thead><tbody>
@forelse($incidents as $incident)<tr><td>#{{ $incident->employee?->employee_number }} - {{ $incident->employee?->last_name_1 }} {{ $incident->employee?->first_names }}</td><td>{{ str_replace('_',' ',ucfirst($incident->type)) }}</td><td>{{ $incident->starts_at->format('d/m/Y H:i') }}</td><td>{{ $incident->ends_at?->format('d/m/Y H:i') ?? '—' }}</td><td>{{ $incident->creator?->name }}</td><td>{{ $incident->notes }}</td></tr>@empty<tr><td colspan="6" class="text-center">Sin incidencias</td></tr>@endforelse
</tbody></table>{{ $incidents->links() }}</div></div>
@endsection
