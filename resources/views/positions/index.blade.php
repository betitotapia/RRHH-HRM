@extends('layouts.admin')

@section('title', 'Departamentos')

@section('content')

    @if (session('success'))
        <div style="color: green; margin-bottom: 10px;">
            {{ session('success') }}
        </div>
    @endif

    {{-- ERRORES --}}
    @if ($errors->any())
        <div style="color: red;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
 <div class="row">
    {{-- FORMULARIO DE ALTA --}}
  <div class="col-md-4">
    <div class="card card-primary">
                    <div class="card-header">
                         <h3>Nuevo puesto</h3>
                    </div>
        <form method="POST" action="{{ route('positions.store') }}" enctype="multipart/form-data">
        @csrf

      <div class="card-body">
                <div class="form-group">
                    <label>Departamento *</label><br>
                    <select class="form-control" name="departament_id" required>
                        <option value="">-- Selecciona --</option>
                        @foreach ($departaments as $departament)
                            <option value="{{ $departament->id }}" {{ old('departament_id') == $departament->id ? 'selected' : '' }}>
                                {{ $departament->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label>Nombre del puesto *</label><br>
                    <input class="form-control" type="text" name="name" value="{{ old('name') }}" required>
                </div>

                <div class="form-group">
                    <label>Clave (opcional)</label><br>
                    <input class="form-control" type="text" name="code" value="{{ old('code') }}">
                </div>
                <div class="form-group"><label>Nivel *</label><input class="form-control" type="text" name="level" maxlength="50" value="{{ old('level') }}" required></div>

                <div class="form-group">
                    <label>Sueldo base (opcional)</label><br>
                    <input class="form-control" type="number" step="0.01" name="base_salary" value="{{ old('base_salary') }}">
                </div>

                <div class="form-group">
                    <label>Descripción</label><br>
                    <textarea class="form-control" name="description">{{ old('description') }}</textarea>
                </div>
                <div class="form-group"><label>Perfil del puesto (PDF)</label><input type="file" name="profile_pdf" class="form-control" accept="application/pdf"><small class="text-muted">Máximo 10 MB.</small></div>

                <div class="form-group">
                    <label>
                        <input class="form-check-input" type="checkbox" name="active" value="1" {{ old('active', true) ? 'checked' : '' }}>
                        Activo
                    </label>
                </div>
            </div>
                <div class="card-footer">
                        <button class="btn btn-primary" type="submit">Guardar</button>
                    </div>
        </form>
     </div>
        
    </div>
    {{-- FIN FORMULARIO DE ALTA --}}
   

    {{-- LISTADO DE PUESTOS --}}
    <div class="col-md-8">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Lista de Puestos</h3>
                </div>
    <div class="card-body p-0 table-responsive">

        <table class="table table-striped table-sm mb-0" style="min-width: 900px;">
        <thead>
            <tr>
                <th>ID</th>
                <th>Puesto</th>
                <th>Departamento</th>
                <th>Clave</th>
                <th>Nivel</th>
                <th>Sueldo base</th>
                <th>Activo</th>
                <th>Perfil PDF</th>
                <th>Creado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($positions as $position)
                <tr>
                    <td>{{ $position->id }}</td>
                    <td>{{ $position->name }}</td>
                    <td>{{ $position->departament?->name }}</td>
                    <td>{{ $position->code }}</td>
                    <td>{{ $position->level }}</td>
                    <td>{{ $position->base_salary }}</td>
                    <td>{{ $position->active ? 'Sí' : 'No' }}</td>
                    <td>@if($position->profile_pdf_path)<a target="_blank" class="btn btn-sm btn-outline-primary" href="{{ route('positions.profile.view',$position) }}">Ver</a> <a class="btn btn-sm btn-outline-secondary" href="{{ route('positions.profile.download',$position) }}">Descargar</a>@else<span class="text-muted">Sin archivo</span>@endif</td>
                    <td class="text-nowrap">{{ $position->created_at?->format('d/m/Y') }}</td>
                    <td class="text-nowrap">
                        <div class="d-flex flex-column align-items-stretch" style="gap: 4px;">
                        <a href="{{ route('positions.edit', $position) }}" class="btn btn-sm btn-info">Editar</a>

                        <form action="{{ route('positions.destroy', $position) }}"
                              method="POST"
                              class="m-0"
                              onsubmit="return confirm('¿Seguro que deseas eliminar este puesto?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger btn-block">Eliminar</button>
                        </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10">No hay puestos registrados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
                @if ($positions->hasPages())
                    <div class="card-footer">
                        {{ $positions->links() }}
                    </div>
                @endif
            </div>
    </div>
   </div> 
    {{-- FIN LISTADO DE PUESTOS --}}
@endsection
