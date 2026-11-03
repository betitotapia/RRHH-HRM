<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'ERP RH') }} | SUMED</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="hold-transition sidebar-mini layout-fixed rh-shell theme-sidebar-{{ auth()->user()?->sidebar_theme ?? 'navy' }}">
<div class="wrapper">
    @include('layouts.partials.navbar')
    @include('layouts.partials.sidebar')
    <div class="content-wrapper">
        @isset($header)
            <div class="content-header"><div class="container-fluid"><div class="rh-page-heading"><div><span class="rh-page-eyebrow">Recursos Humanos</span><div class="rh-component-title">{{ $header }}</div></div><div class="rh-date d-none d-md-flex"><i class="far fa-calendar-alt"></i><span>{{ now()->locale('es')->translatedFormat('d M Y') }}</span></div></div></div></div>
        @endisset
        <section class="content pb-4"><div class="container-fluid">{{ $slot }}</div></section>
    </div>
    <footer class="main-footer"><strong>ERP RH &copy; {{ date('Y') }} SUMED</strong><span class="float-right d-none d-sm-inline">Gestión de talento</span></footer>
</div>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>
