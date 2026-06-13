<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel Administrador') - Sabor & Tradición</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <link rel="stylesheet" href="{{ asset('css/home2.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body>

    <div class="admin-container">
        <aside class="admin-sidebar">
            <div class="sidebar-brand">
                <img src="{{ asset('img/LogoRestaurant.png') }}" alt="Logo" class="sidebar-logo">
                <h2>Sabor & Tradición</h2>
            </div>

            <nav class="sidebar-nav">
                <a href="{{ route('admin.index') }}" class="nav-item {{ request()->routeIs('admin.index') ? 'active' : '' }}">Dashboard</a>
                <a href="{{ route('admin.usuarios') }}" class="nav-item {{ request()->routeIs('admin.usuarios') ? 'active' : '' }}">Usuarios</a>
                <a href="{{ route('admin.menu') }}" class="nav-item {{ request()->routeIs('admin.menu') ? 'active' : '' }}">Menú</a>
                <a href="{{ route('admin.reservas') }}" class="nav-item {{ request()->routeIs('admin.reservas') ? 'active' : '' }}">Reservas</a>
                <a href="{{ route('admin.pedidos') }}" class="nav-item {{ request()->routeIs('admin.pedidos') ? 'active' : '' }}">Pedidos</a>
            </nav>

            <div class="sidebar-footer">
                <form action="{{ route('logout') }}" method="POST" id="logout-form">
                    @csrf
                    <button type="button" class="btn btn-secondary logout-button" onclick="adminLogout()">Cerrar Sesión</button>
                </form>
            </div>
        </aside>

        <main class="admin-main">
            <header class="admin-header">
                <div>
                    <h1>@yield('title', 'Panel Administrador')</h1>
                    <p class="admin-subtitle">Bienvenido, {{ Auth::user()->name }}. Gestiona el restaurante desde aquí.</p>
                </div>
            </header>

            <!-- Session alerts will be handled by SweetAlert in JS -->

            @yield('content')
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/loader.js') }}"></script>
    <script>
        function adminLogout() {
            mostrarCarga('Cerrando sesión...');
            setTimeout(function() {
                document.getElementById('logout-form').submit();
            }, 900);
        }

        document.addEventListener('DOMContentLoaded', function() {
            @if(session('success'))
                Swal.fire({
                    title: '¡Éxito!',
                    text: "{{ session('success') }}",
                    icon: 'success',
                    background: '#1a1a1a',
                    color: '#f5f5f5',
                    confirmButtonColor: '#c29545',
                    customClass: { popup: 'swal-on-top' }
                });
            @endif
            @if(session('error'))
                Swal.fire({
                    title: 'Error',
                    text: "{{ session('error') }}",
                    icon: 'error',
                    background: '#1a1a1a',
                    color: '#f5f5f5',
                    confirmButtonColor: '#c29545',
                    customClass: { popup: 'swal-on-top' }
                });
            @endif
        });
    </script>
    @yield('scripts')
</body>
</html>
