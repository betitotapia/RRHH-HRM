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
    
                <form method="POST" action="{{ route('departaments.update', $departament) }}">
        @csrf
        @method('PUT')

         <div class="card-body">
                        <div class="form-group">
                            <label>Nombre *</label>
                            <input
                                type="text"
                                name="name"
                                class="form-control"
                                value="{{ old('name', $departament->name) }}"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label>Clave (opcional)</label>
                            <input
                                type="text"
                                name="code"
                                class="form-control"
                                value="{{ old('code', $departament->code) }}"
                            >
                        </div>

                        <div class="form-group">
                            <label>Descripción</label>
                            <textarea
                                name="description"
                                class="form-control"
                                rows="3"
                            >{{ old('description', $departament->description) }}</textarea>
                        </div>

                        <div class="form-group form-check">
                            <input
                                type="checkbox"
                                name="active"
                                value="1"
                                class="form-check-input"
                                id="activeCheck"
                                {{ old('active', $departament->active) ? 'checked' : '' }}
                            >
                            <label class="form-check-label" for="activeCheck">Activo</label>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">Actualizar</button>
                        <a href="{{ route('departaments.index') }}" class="btn btn-secondary">Cancelar</a>
                    </div>
    </form>
   </div>
        </div>
    </div>

@endsection