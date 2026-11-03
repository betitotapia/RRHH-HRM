<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Login') - ERP RH</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- AdminLTE + Bootstrap + FontAwesome (CDN) --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">

    {{-- Tus assets de Vite (por si los necesitas) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
    body.login-page {
        background: url("{{ asset('img/bg-login.jpg') }}") no-repeat center center fixed;
        background-size: cover;
    }

    /* Fondo semitransparente en la tarjeta para que se lea bien sobre la imagen */
    .login-box .card {
        box-shadow: 0 0 15px rgba(0,0,0,0.3);
        background-color: rgba(255,255,255,0.95);
    }
</style>
</head>
<body class="hold-transition login-page">

    <div class="login-box">
        <div class="login-logo">
        {{-- Logo encima del texto --}}
        <img src="{{ asset('img/logo-sumed.png') }}" alt="Logo" style="max-height: 80px; display:block; margin:0 auto 10px;">

        <span><b>ERP</b> RH</span>
    </div>

    @yield('content')
    </div>

    {{-- JS de AdminLTE --}}
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html>
