@extends('layouts.directory')

@section('title', 'Directorio SUMED')

@section('content')

    @if ($search)
        {{-- MODO BÚSQUEDA --}}
        <h3>Resultados para: "{{ $search }}"</h3>
        <p class="text-muted">{{ $employees->count() }} resultado(s)</p>

        @forelse ($employees as $employee)
            @php
                $fullName  = trim($employee->last_name_1.' '.$employee->last_name_2.' '.$employee->first_names);
                $jobTitle  = $employee->position->name ?? 'Colaborador';
                $deptName  = $employee->departament->name ?? '';
                $photoPath = $employee->photo_path ?? null; // ajusta al campo real
                $photoUrl  = $photoPath
                    ? asset('storage/' . ltrim($photoPath, '/'))
                    : 'https://via.placeholder.com/70x70?text=EMP';
            @endphp

            <div class="media mb-4 employee-card">
                <img src="{{ asset('storage/' . $employee->photo) }}" class="mr-3" alt="{{ $fullName }}">

                <div class="media-body">
                    <h4 class="mt-0 mb-1">{{ $jobTitle }}</h4>
                    <div>{{ $fullName }}</div>
                    @if ($deptName)
                        <div class="text-muted">{{ $deptName }}</div>
                    @endif

                    <div class="mt-1">
                        📞 {{ $employee->corporate_phone ?? '(222) 604 4913 Ext.111' }}
                    </div>
                    <div class="mt-1">
                        ✉ {{ $employee->enterprise_email ?? 'recursos.humanos@sumed.com.mx' }}
                    </div>

                    <a href="{{ route('directory.employee', $employee) }}"
                       class="btn btn-sm btn-outline-primary mt-2">
                        Más información →
                    </a>
                </div>
            </div>
        @empty
            <p class="text-muted">No se encontraron colaboradores con esos criterios.</p>
        @endforelse

    @else
        {{-- MODO NORMAL: TABS --}}
        <ul class="nav nav-tabs mb-3">
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'departments' ? 'active' : '' }}"
                   href="{{ route('directory.index', ['tab' => 'departments']) }}">
                    Departamentos
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab === 'commissions' ? 'active' : '' }}"
                   href="{{ route('directory.index', ['tab' => 'commissions']) }}">
                    Comisiones
                </a>
            </li>
        </ul>

        @if ($tab === 'departments')
            <h3>Departamentos</h3>
            <ul>
                @foreach ($departaments as $departament)
                    <li>
                        <a href="{{ route('directory.department', $departament) }}">
                            {{ $departament->name }}
                        </a>
                    </li>
                @endforeach
            </ul>
        @else
            <h3>Comisiones</h3>
            <ul>
                @foreach ($commissions as $commission)
                    <li>
                        <a href="{{ route('directory.commission', $commission) }}">
                            {{ $commission->name }}
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    @endif

@endsection
