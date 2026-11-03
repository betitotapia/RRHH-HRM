@extends('layouts.directory')

@section('title', 'Comisión - ' . $commission->name)

@section('content')

    <div class="row">
        <div class="col-md-9">

            <h2>{{ $commission->name }}</h2>
            <p class="text-muted">{{ $commission->employees->count() }} miembro(s)</p>

            @forelse ($commission->employees as $employee)
                @php
                    $fullName = trim($employee->last_name_1.' '.$employee->last_name_2.' '.$employee->first_names);
                    $positionName = $employee->position->name ?? '';
                    $role = $employee->pivot->role; // presidente, vocal, etc.
                @endphp

                <div class="media mb-4 employee-card">
                    <img src="{{ asset('storage/' . $employee->photo) }}"
                         class="mr-3" alt="{{ $fullName }}">

                    <div class="media-body">
                        <h4 class="mt-0 mb-1">
                            {{ $positionName ?: 'Colaborador' }}
                        </h4>
                        <div>{{ $fullName }}</div>

                        @if ($role)
                            <div class="mt-1">
                                <strong>Cargo en la comisión:</strong> {{ ucfirst($role) }}
                            </div>
                        @endif

                        <div class="mt-1">
                            📞 {{ $employee->corporate_phone ?? '(222) 604 4913 Ext.111' }}
                        </div>
                        <div class="mt-1">
                            ✉ {{ $employee->enterprise_email ?? 'recursos.humanos@sumed.com.mx' }}
                        </div>

                        <a  href="{{ route('directory.employee', $employee) }}"
                           class="btn btn-sm btn-outline-primary mt-2" style="font-size:1.2em;">
                            Más información →
                        </a>
                    </div>
                </div>
            @empty
                <p class="text-muted">No hay miembros registrados en esta comisión.</p>
            @endforelse

        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    COMISIONES
                </div>
                <ul class="list-group list-group-flush">
                    @foreach ($commissions as $com)
                        <li class="list-group-item {{ $com->id === $commission->id ? 'font-weight-bold' : '' }}">
                            <a href="{{ route('directory.commission', $com) }}">
                                {{ $com->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

@endsection
