<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Directorio SUMED')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Bootstrap 4 (o el que ya uses) --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    <style>
        body {
            padding-top: 15px;
        }
        .top-bar {
            background-color: #0b4f8a;
            color: #fff;
        }
        .top-bar a.nav-link {
            color: #fff;
        }
        .top-bar .nav-link.active {
            font-weight: bold;
            text-decoration: underline;
        }
        .employee-card img {
            width: 70px;
            height: 70px;
            border-radius: 4px;
            object-fit: cover;
        }
        body.directory-body {
        font-size: 1.5rem;     /* default 1rem → hacemos 10% más grande */
        line-height: 1.6;
    }
.directory-title {
        font-size: 2rem;       /* 32px */
        font-weight: 600;
    }

    .directory-subtitle {
        font-size: 1.3rem;
        font-weight: 500;
    }
     .nav-tabs .nav-link {
        font-size: 1.5rem;
        padding: 10px 18px;
    }

    .nav-tabs .nav-link.active {
        font-weight: 600;
        border-color: #0b4f72 #0b4f72 #ffffff; /* línea azul */
        color: #0b4f72;
    }
    
    </style>
</head>
<body class="directory-body">

<div class="container-xxl py-3">
<div class="row">
     <div class="col-lg-11 mx-auto">
    {{-- Encabezado --}}
    <header class="mb-3">
        <h2 class="mb-0">Suministros Médicos de México S.A. de C.V.</h2>
        {{-- <small>Directorio SUMED</small> --}}
    </header>

    {{-- Barra azul con tabs --}}
    <nav class="navbar navbar-expand top-bar mb-4">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item">
                <a href="{{ route('directory.index', ['tab' => 'departments']) }}"
                   class="nav-link {{ (request('tab', 'departments') === 'departments') ? 'active' : '' }}">
                    Directorio
                </a>
            </li>
            {{-- Futuro: Buzón, etc. --}}
            <li class="nav-item">
                <a href="#" class="nav-link disabled">Buzón</a>
            </li>
        </ul>

        {{-- Botón descargar + búsqueda (placeholder) --}}
        <form class="form-inline my-2 my-lg-0" method="GET" action="{{ route('directory.index') }}">
    {{-- botón descargar --}}
    <a href="{{ route('directory.download') }}" class="btn btn-primary mr-2">
        Descargar Directorio
    </a>

    {{-- input de búsqueda --}}
    <input class="form-control mr-sm-2"
           type="search"
           name="search"
           placeholder="Buscar ..."
           aria-label="Buscar"
           value="{{ request('search') }}">

    {{-- mantener la pestaña actual al buscar --}}
    <input type="hidden" name="tab" value="{{ request('tab', 'departments') }}">

    <button class="btn btn-light my-2 my-sm-0" type="submit">
        🔍
    </button>
</form>
    </nav>

    @yield('content')
</div>
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
