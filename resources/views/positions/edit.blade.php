@extends('layouts.admin')

@section('title', 'Editar departamento')

@section('content')

    @if (session('success'))
        <div style="color: green; margin-bottom: 10px;">
            {{ session('success') }}
        </div>
    @endif

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
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">Editar departamento</h3>
                </div>
    

    <form method="POST" action="{{ route('positions.update', $position) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

    <div class="card-body">
       <div class="form-group">
            <label>Departamento *</label><br>
            <select name="departament_id" class="form-control" required>
                <option value="">-- Selecciona --</option>
                @foreach ($departaments as $departament)
                    <option value="{{ $departament->id }}"
                        {{ old('departament_id', $position->departament_id) == $departament->id ? 'selected' : '' }}>
                        {{ $departament->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="form-group">
            <label>Nombre del puesto *</label><br>
            <input type="text" name="name" class="form-control" value="{{ old('name', $position->name) }}" required>
        </div>

        <div class="form-group">
            <label>Clave (opcional)</label><br>
            <input type="text" name="code" class="form-control" value="{{ old('code', $position->code) }}">
        </div>
        <div class="form-group"><label>Nivel *</label><input type="text" name="level" class="form-control" maxlength="50" value="{{ old('level', $position->level) }}" required></div>

        <div class="form-group">
            <label>Sueldo base (opcional)</label><br>
            <input type="number" step="0.01" name="base_salary" class="form-control" value="{{ old('base_salary', $position->base_salary) }}">
        </div>

        <div class="form-group">
            <label>Descripción</label><br>
            <textarea name="description" class="form-control">{{ old('description', $position->description) }}</textarea>
        </div>
        <div class="form-group"><label>Perfil del puesto (PDF)</label><input type="file" name="profile_pdf" class="form-control" accept="application/pdf"><small class="text-muted">Subir un archivo nuevo reemplazará el actual. Máximo 10 MB.</small>@if($position->profile_pdf_path)<div class="mt-2"><a target="_blank" href="{{ route('positions.profile.view',$position) }}" class="btn btn-sm btn-outline-primary">Ver PDF actual</a> <a href="{{ route('positions.profile.download',$position) }}" class="btn btn-sm btn-outline-secondary">Descargar</a></div>@endif</div>

        <div class="form-group">
            <label>
                <input
                    type="checkbox"
                    name="active"
                    value="1"
                    {{ old('active', $position->active) ? 'checked' : '' }}
                >
                Activo
            </label>
        </div>

        <button type="submit">Actualizar</button>
        <a href="{{ route('positions.index') }}">Cancelar</a>
    </form>

   </div>
        </div>
    </div>

@endsection
