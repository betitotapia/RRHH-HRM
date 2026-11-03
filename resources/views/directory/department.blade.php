@extends('layouts.directory')

@section('title', 'Directorio - ' . $departament->name)

@section('content')

    <div class="row">
        <div class="col-md-9">

            <h2>{{ $departament->name }}</h2>
            <p class="text-muted">{{ $employees->count() }} resultado(s)</p>

            @forelse ($employees as $employee)
                @php
                    $fullName = trim($employee->last_name_1.' '.$employee->last_name_2.' '.$employee->first_names);
                    $positionName = $employee->positions->name ?? '';
                @endphp

                <div class="media mb-4 employee-card">
                    {{-- Foto --}}
                    <img src="{{ asset('storage/' . $employee->photo) }}"
                         class="mr-3" alt="{{ $fullName }}">

                    <div class="media-body">
                        <h4 class="mt-0 mb-1">
                            {{ $positionName ?: 'Colaborador' }}
                        </h4>
                        <div>{{ $fullName }}</div>

                        {{-- Teléfono: si tiene corporativo, si no teléfono general --}}
                        <div class="mt-1">
                            📞
                            @if ($employee->phone)
                                {{ $employee->phone }}
                            @else
                                (222) 604 4913 Ext.111 {{-- ajusta al tuyo --}}
                            @endif
                        </div>

                        <div class="mt-1">
                            ✉
                            {{ $employee->email ?? 'recursos.humanos@sumed.com.mx' }}
                        </div>

                        <a href="{{ route('directory.employee', $employee) }}"
                           class="btn btn-sm btn-outline-primary mt-2">
                            Más información →
                        </a>
                    </div>
                </div>
            @empty
                <p class="text-muted">No hay colaboradores registrados en este departamento.</p>
            @endforelse

        </div>

        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    CATEGORÍAS
                </div>
                <ul class="list-group list-group-flush">
                    @foreach ($departaments as $dep)
                        <li class="list-group-item {{ $dep->id === $departament->id ? 'font-weight-bold' : '' }}">
                            <a href="{{ route('directory.department', $dep) }}">
                                {{ $dep->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>

@endsection
