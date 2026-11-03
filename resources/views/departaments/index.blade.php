@extends('layouts.admin')

@section('title', 'Departamentos')

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
        {{-- Formulario de alta --}}
        <div class="col-md-4">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Nuevo departamento</h3>
                </div>
                <form method="POST" action="{{ route('departaments.store') }}">
                    @csrf
                    <div class="card-body"> 
                        <div class="form-group">
                            <label>Nombre *</label>
                            <input type="text" name="name" class="form-control"
                                   value="{{ old('name') }}" required>
                        </div>

                        <div class="form-group">
                            <label>Clave (opcional)</label>
                            <input type="text" name="code" class="form-control"
                                   value="{{ old('code') }}">
                        </div>

                        <div class="form-group">
                            <label>Descripción</label>
                            <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                        </div>

                        <div class="form-group form-check">
                            <input type="checkbox" name="active" value="1"
                                   class="form-check-input" id="activeCheck"
                                   {{ old('active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="activeCheck">Activo</label>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-primary" type="submit">Guardar</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Tabla de departamentos --}}
        <div class="col-md-8">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Lista de departamentos</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Clave</th>
                            <th>Activo</th>
                         
                            <th style="width: 140px;">Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse ($departaments as $departament)
                            <tr>
                                <td>{{ $departament->id }}</td>
                                <td>{{ $departament->name }}</td>
                                <td>{{ $departament->code }}</td>
                                <td>
                                    @if ($departament->active)
                                        <span class="badge badge-success">Sí</span>
                                    @else
                                        <span class="badge badge-secondary">No</span>
                                    @endif
                                </td>
                               
                                <td>
                                    <a href="{{ route('departaments.edit', $departament) }}" class="btn btn-sm btn-info">
                                        Editar
                                    </a>
                                    <form action="{{ route('departaments.destroy', $departament) }}"
                                          method="POST"
                                          style="display:inline"
                                          onsubmit="return confirm('¿Seguro que deseas eliminar este departamento?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            Eliminar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">No hay departamentos registrados.</td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($departaments->hasPages())
                    <div class="card-footer">
                        {{ $departaments->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

@endsection
