@extends('layouts.admin')

@section('title', 'Editar comisión')

@section('content')

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
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

    <div class="card card-primary">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3 class="card-title">Editar comisión</h3>
            <a href="{{ route('commissions.index') }}" class="btn btn-sm btn-secondary">
                Volver al listado
            </a>
        </div>

        <form method="POST" action="{{ route('commissions.update', $commission) }}">
            @csrf
            @method('PUT')

            <div class="card-body">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Nombre *</label>
                        <input type="text"
                               name="name"
                               class="form-control"
                               value="{{ old('name', $commission->name) }}"
                               required>
                    </div>

                    <div class="form-group col-md-3">
                        <label>Clave</label>
                        <input type="text"
                               name="code"
                               class="form-control"
                               value="{{ old('code', $commission->code) }}">
                    </div>

                    <div class="form-group col-md-3">
                        <label>Activa</label>
                        <div class="form-check mt-2">
                            <input type="checkbox"
                                   name="active"
                                   id="active"
                                   value="1"
                                   class="form-check-input"
                                   {{ old('active', $commission->active) ? 'checked' : '' }}>
                            <label for="active" class="form-check-label">Sí</label>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Descripción</label>
                    <textarea name="description"
                              class="form-control"
                              rows="3">{{ old('description', $commission->description) }}</textarea>
                </div>

               <hr>

<h5>Miembros de la comisión</h5>
<p class="text-muted small">
    Marca los empleados que pertenecen a esta comisión y asigna el cargo correspondiente.
</p>

<div class="table-responsive">
    <table class="table table-sm table-striped">
        <thead>
            <tr>
                <th style="width: 40px;">Miembro</th>
                <th>Empleado</th>
                <th>No. Empleado</th>
                <th style="width: 200px;">Cargo en la comisión</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($employees as $employee)
                @php
                    $fullName = trim($employee->last_name_1.' '.$employee->last_name_2.' '.$employee->first_names);
                    $isMember = array_key_exists($employee->id, $membersById);
                    $currentRole = $membersById[$employee->id] ?? null;
                @endphp
                <tr>
                    <td class="text-center">
                        <input type="checkbox"
                               name="employee_ids[]"
                               value="{{ $employee->id }}"
                               {{ $isMember ? 'checked' : '' }}>
                    </td>
                    <td>{{ $fullName }}</td>
                    <td>{{ $employee->employee_number }}</td>
                    <td>
                        <select name="member_roles[{{ $employee->id }}]" class="form-control form-control-sm">
                            <option value="">-- Sin cargo específico --</option>
                            @foreach ($roles as $value => $label)
                                <option value="{{ $value }}"
                                    {{ $currentRole === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

            <div class="card-footer d-flex justify-content-between">
                <button type="submit" class="btn btn-primary">
                    Guardar cambios
                </button>
                <a href="{{ route('commissions.index') }}" class="btn btn-secondary">
                    Cancelar
                </a>
            </div>
        </form>
    </div>

@endsection
