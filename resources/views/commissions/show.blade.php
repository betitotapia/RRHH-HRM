@extends('layouts.admin')

@section('title', 'Detalle comisión')

@section('content')

    <div class="row">
        <div class="col-12">

            <div class="card card-primary card-outline">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">
                        Detalle de la comisión
                    </h3>
                    <div>
                        <a href="{{ route('commissions.edit', $commission) }}" class="btn btn-sm btn-info">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <a href="{{ route('commissions.index') }}" class="btn btn-sm btn-secondary">
                            Volver al listado
                        </a>
                    </div>
                </div>

                <div class="card-body">

                    {{-- ENCABEZADO --}}
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <h4 class="mb-1">
                                {{ $commission->name }}
                            </h4>
                            <p class="mb-1">
                                <strong>Clave:</strong> {{ $commission->code ?? '-' }}
                            </p>
                            <p class="mb-1">
                                <strong>Estado:</strong>
                                @if ($commission->active)
                                    <span class="badge badge-success">Activa</span>
                                @else
                                    <span class="badge badge-secondary">Inactiva</span>
                                @endif
                            </p>
                            <p class="mb-1">
                                <strong>Creada:</strong>
                                {{ $commission->created_at?->format('d-m-Y H:i') }}
                            </p>
                        </div>
                        <div class="col-md-4">
                            <p class="mb-1"><strong>Total de miembros:</strong> {{ $commission->employees->count() }}</p>
                        </div>
                    </div>

                    @if ($commission->description)
                        <div class="mb-3">
                            <strong>Descripción:</strong>
                            <p class="mb-0">{{ $commission->description }}</p>
                        </div>
                    @endif

                    <hr>

                    {{-- LISTA DE MIEMBROS --}}
                    <h5>Miembros de la comisión</h5>

                    @if ($commission->employees->isEmpty())
                        <p class="text-muted">No hay miembros asignados a esta comisión.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-sm">
                                <thead>
                                    <tr>
                                        <th>Empleado</th>
                                        <th>No. Empleado</th>
                                        <th>Cargo en la comisión</th>
                                        <th>Fecha de asignación</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($commission->employees as $employee)
                                        @php
                                            $fullName = trim($employee->last_name_1.' '.$employee->last_name_2.' '.$employee->first_names);
                                            $pivotRole = $employee->pivot->role;
                                        @endphp
                                        <tr>
                                            <td>{{ $fullName }}</td>
                                            <td>{{ $employee->employee_number }}</td>
                                            <td>
                                                @if ($pivotRole)
                                                    {{ $roles[$pivotRole] ?? $pivotRole }}
                                                @else
                                                    <span class="text-muted">Miembro</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $employee->pivot->created_at?->format('d-m-Y') ?? '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                </div>
            </div>

        </div>
    </div>

@endsection
