@extends('layouts.admin')

@section('title', 'Empleados')

@section('content')

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">
       
        {{-- LISTADO DE EMPLEADOS --}}
        <div class="col-lg-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Lista de empleados</h3>
                </div>

                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>Fotografía</th>
                                <th># Emp</th>
                                <th>Nombre</th>
                                <th>Depto / Puesto</th>
                                <th>Teléfono</th>
                                <th>Correo</th>
                                {{-- <th>Ingreso</th> --}}
                                <th>Activo</th>
                                <th>Acceso al sistema</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($employees as $employee)
                                <tr>
                                     <td>
                                        @if ($employee->photo)
                                            <img src="{{ asset('storage/' . $employee->photo) }}"
                                                class="img-circle elevation-2"
                                                style="width: 40px; height: 40px; object-fit: cover;"
                                            >
                                        @else
                                            <span class="badge badge-secondary">Sin foto</span>
                                        @endif
                                    </td>
                                    <td>{{ $employee->employee_number }}</td>
                                    <td>
                                        <a href="{{ route('employees.show', $employee) }}">
                                            {{ $employee->last_name_1 }}
                                            {{ $employee->last_name_2 }}
                                            {{ $employee->first_names }}
                                        </a>
                                    </td>
                                    <td>
                                        {{ $employee->departament?->name }}<br>
                                        <small>{{ $employee->positions?->name }}</small>
                                    </td>
                                    <td>{{ $employee->phone }}</td>
                                    <td>{{ $employee->email }}</td>
                                    {{-- <td>{{ $employee->hire_date }}</td> --}}
                                    <td>
                                        @if ($employee->active)
                                            <span class="badge badge-success">Sí</span>
                                        @else
                                            <span class="badge badge-secondary">No</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($employee->user)
                                            <span class="badge badge-{{ $employee->user->is_active ? 'success' : 'secondary' }}">{{ $employee->user->is_active ? 'Activo' : 'Inactivo' }}</span>
                                            <br><small>{{ $employee->user->email }}</small>
                                        @else
                                            <span class="badge badge-light">Sin usuario</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('employees.edit', $employee) }}"
                                           class="btn btn-sm btn-info">
                                            Editar
                                        </a>

                                        <form action="{{ route('employees.destroy', $employee) }}"
                                              method="POST"
                                              style="display:inline"
                                              onsubmit="return confirm('¿Eliminar este empleado?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-danger" type="submit">
                                                Eliminar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center">No hay empleados registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($employees->hasPages())
                    <div class="card-footer">
                        {{ $employees->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

@endsection
