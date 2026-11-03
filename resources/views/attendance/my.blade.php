@extends('layouts.admin')

@section('title', 'Mi asistencia')

@section('content')

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card card-outline card-primary">
    <div class="card-header">
        <h3 class="card-title">Mi asistencia - {{ $employee->employee_number }}</h3>
    </div>

    <div class="card-body">

        <div class="mb-3">
            <strong>{{ trim($employee->last_name_1.' '.$employee->last_name_2.' '.$employee->first_names) }}</strong>
        </div>

        <div class="mb-3">
            @php
                $canCheckIn  = !($last && $last->type === 'in');
                $canCheckOut = ($last && $last->type === 'in');
            @endphp

            <form method="POST" action="{{ route('attendance.checkin') }}" style="display:inline;">
                @csrf
                <button class="btn btn-success" type="submit" {{ $canCheckIn ? '' : 'disabled' }}>
                    Registrar entrada
                </button>
            </form>

            <form method="POST" action="{{ route('attendance.checkout') }}" style="display:inline;">
                @csrf
                <button class="btn btn-danger" type="submit" {{ $canCheckOut ? '' : 'disabled' }}>
                    Registrar salida
                </button>
            </form>
        </div>

        <hr>

        <form method="GET" class="form-inline mb-3">
            <label class="mr-2">Desde</label>
            <input type="date" name="from" class="form-control mr-3" value="{{ $from->toDateString() }}">
            <label class="mr-2">Hasta</label>
            <input type="date" name="to" class="form-control mr-3" value="{{ $to->toDateString() }}">
            <button class="btn btn-primary" type="submit">Filtrar</button>
        </form>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Fecha/Hora</th>
                        <th>Tipo</th>
                        <th>Origen</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $r)
                        <tr>
                            <td>{{ $r->registered_at->format('d-m-Y H:i:s') }}</td>
                            <td>{{ $r->type === 'in' ? 'Entrada' : 'Salida' }}</td>
                            <td>{{ $r->source }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3">Sin registros</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $records->links() }}
    </div>
</div>

@endsection
