<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Sabor & Tradición</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body>

    <div class="auth-container">
        <div style="text-align: left; margin-bottom: 1rem;">
            <a href="{{ route('home') }}" class="back-link" style="margin-top: 0;">← Volver al inicio</a>
        </div>

        <h2>Bienvenido</h2>
        <p>Inicia sesión para continuar</p>

        <form id="loginForm" action="{{ route('login.post') }}" method="POST">
            @csrf
            <div id="error-message" style="color: #e84c3d; background: rgba(232, 76, 61, 0.1); padding: 10px; border-radius: 5px; margin-bottom: 1rem; display: none; font-size: 0.9rem; border: 1px solid rgba(232, 76, 61, 0.3);"></div>
            
            @if (session('success'))
                <div style="color: #2ecc71; background: rgba(46, 204, 113, 0.1); padding: 10px; border-radius: 5px; margin-bottom: 1rem; font-size: 0.9rem; border: 1px solid rgba(46, 204, 113, 0.3);">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div style="color: #e84c3d; background: rgba(232, 76, 61, 0.1); padding: 10px; border-radius: 5px; margin-bottom: 1rem; font-size: 0.9rem; border: 1px solid rgba(232, 76, 61, 0.3);">
                    <ul style="margin: 0; padding-left: 20px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="tu@correo.com" value="{{ old('email') }}" required>
                <span id="emailError" class="error-text hidden"></span>
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
                <span id="passwordError" class="error-text hidden"></span>
            </div>

            <button type="submit" class="btn-primary">Iniciar Sesión</button>
        </form>

        <div style="margin-top: 2rem;">
            <p style="margin-bottom: 0;">¿Aún no tienes cuenta? <a href="{{ route('register') }}" style="color: var(--primary); text-decoration: none; font-weight: 500;">Regístrate ahora</a></p>
        </div>
    </div>

    <!-- Script de Validaciones -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/loader.js') }}?v={{ time() }}"></script>
    <script src="{{ asset('js/ScriptLogin.js') }}?v={{ time() }}"></script>
</body>
</html>
