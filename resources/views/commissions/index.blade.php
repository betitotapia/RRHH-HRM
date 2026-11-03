@extends('layouts.admin')

@section('title', 'Comisiones')

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

    <div class="row">
        {{-- FORMULARIO NUEVA COMISIÓN --}}
        <div class="col-md-4">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Nueva comisión</h3>
                </div>

                <form method="POST" action="{{ route('commissions.store') }}">
                    @csrf

                    <div class="card-body">
                        <div class="form-group">
                            <label>Nombre *</label>
                            <input type="text"
                                   name="name"
                                   class="form-control"
                                   value="{{ old('name') }}"
                                   required>
                        </div>

                        <div class="form-group">
                            <label>Clave (opcional)</label>
                            <input type="text"
                                   name="code"
                                   class="form-control"
                                   value="{{ old('code') }}">
                        </div>

                        <div class="form-group">
                            <label>Descripción</label>
                            <textarea name="description"
                                      class="form-control"
                                      rows="3">{{ old('description') }}</textarea>
                        </div>

                        <div class="form-group">
                            <div class="form-check">
                                <input type="checkbox"
                                       name="active"
                                       id="active"
                                       value="1"
                                       class="form-check-input"
                                       {{ old('active', true) ? 'checked' : '' }}>
                                <label for="active" class="form-check-label">
                                    Activa
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button class="btn btn-primary" type="submit">Guardar</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- LISTA DE COMISIONES --}}
        <div class="col-md-8">
            <div class="card card-outline card-primary">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Listado de comisiones</h3>

                    <form method="GET" action="{{ route('commissions.index') }}" class="card-tools">
                        <div class="input-group input-group-sm" style="width: 220px;">
                            <input type="text"
                                   name="search"
                                   class="form-control float-right"
                                   placeholder="Buscar nombre o clave"
                                   value="{{ $search }}">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-default">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="card-body p-0">
                    <table class="table table-striped mb-0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Clave</th>
                                <th>Activa</th>
                                <th>Miembros</th>
                                <th>Creada</th>
                                <th style="width: 150px;">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($commissions as $commission)
                                <tr>
                                    <td>{{ $commission->id }}</td>
                                    <td>{{ $commission->name }}</td>
                                    <td>{{ $commission->code }}</td>
                                    <td>
                                        @if ($commission->active)
                                            <span class="badge badge-success">Sí</span>
                                        @else
                                            <span class="badge badge-secondary">No</span>
                                        @endif
                                    </td>
                                    <td>{{ $commission->employees_count }}</td>
                                    <td>{{ $commission->created_at?->format('d-m-Y') }}</td>
                                    <td>
                                        <a href="{{ route('commissions.show', $commission) }}"
                                        class="btn btn-sm btn-secondary">
                                            Ver
                                        </a>

                                        <a href="{{ route('commissions.edit', $commission) }}"
                                        class="btn btn-sm btn-info">
                                            Editar
                                        </a>

                                        <form action="{{ route('commissions.destroy', $commission) }}"
                                            method="POST"
                                            style="display:inline"
                                            onsubmit="return confirm('¿Eliminar esta comisión?');">
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
                                    <td colspan="7" class="text-center">
                                        No hay comisiones registradas.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($commissions->hasPages())
                    <div class="card-footer">
                        {{ $commissions->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

@endsection
