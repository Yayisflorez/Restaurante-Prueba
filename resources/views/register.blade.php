<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrarse - Sabor & Tradición</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
</head>
<body>

    <div class="auth-container" style="max-width: 600px;">
        <div style="text-align: left; margin-bottom: 1rem;">
            <a href="{{ route('home') }}" class="back-link" style="margin-top: 0;">← Volver al inicio</a>
        </div>

        <h2>Únete a Nosotros</h2>
        <p>Crea tu cuenta en Sabor & Tradición</p>

        <form id="registerForm" action="{{ route('register.post') }}" method="POST">
            @csrf
            <div id="error-message" style="color: #e84c3d; background: rgba(232, 76, 61, 0.1); padding: 10px; border-radius: 5px; margin-bottom: 1rem; display: none; font-size: 0.9rem; border: 1px solid rgba(232, 76, 61, 0.3);"></div>

            @if ($errors->any())
                <div style="color: #e84c3d; background: rgba(232, 76, 61, 0.1); padding: 10px; border-radius: 5px; margin-bottom: 1rem; font-size: 0.9rem; border: 1px solid rgba(232, 76, 61, 0.3);">
                    <ul style="margin: 0; padding-left: 20px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="form-row">
                <div class="form-group">
                    <label for="tipo_documento">Tipo de Documento</label>
                    <select name="tipo_documento_id" id="tipo_documento" class="form-control" style="color: #fff; background: rgba(0,0,0,0.5);" required>
                        <option value="">Seleccione un tipo...</option>
                        @foreach ($tipo_documentos as $tipo)
                            <option value="{{ $tipo->id }}">{{ $tipo->sigla }} - {{ $tipo->nombre }}</option>
                        @endforeach
                    </select>
                    <span id="tipoDocError" class="error-text hidden"></span>
                </div>
                <div class="form-group">
                    <label for="numero_documento">Número de Documento</label>
                    <input type="number" name="numero_documento" id="numero_documento" class="form-control" placeholder="Ej: 123456789" value="{{ old('numero_documento') }}" required>
                    <span id="numDocError" class="error-text hidden"></span>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="name">Nombre</label>
                    <input type="text" name="name" id="name" class="form-control" placeholder="Ej: Juan" value="{{ old('name') }}" required>
                    <span id="nameError" class="error-text hidden"></span>
                </div>
                <div class="form-group">
                    <label for="lastname">Apellido</label>
                    <input type="text" name="lastname" id="lastname" class="form-control" placeholder="Ej: Pérez" value="{{ old('lastname') }}" required>
                    <span id="lastnameError" class="error-text hidden"></span>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="telefono">Teléfono</label>
                    <input type="tel" name="telefono" id="telefono" class="form-control" placeholder="Ej: 3001234567" value="{{ old('telefono') }}" required>
                    <span id="telefonoError" class="error-text hidden"></span>
                </div>
                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="tu@correo.com" value="{{ old('email') }}" required>
                    <span id="emailError" class="error-text hidden"></span>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
                    <small style="color: var(--text-muted); font-size: 0.75rem;">Debe tener al menos 8 caracteres, 1 mayúscula y 1 número.</small>
                    <span id="passwordError" class="error-text hidden"></span>
                </div>
                <div class="form-group">
                    <label for="password_confirm">Confirmar Contraseña</label>
                    <input type="password" name="password_confirmation" id="password_confirm" class="form-control" placeholder="••••••••" required>
                    <span id="passwordConfirmError" class="error-text hidden"></span>
                </div>
            </div>

            <button type="submit" class="btn-primary">Crear Cuenta</button>
        </form>

        <div style="margin-top: 2rem;">
            <p style="margin-bottom: 0;">¿Ya tienes cuenta? <a href="{{ route('login') }}" style="color: var(--primary); text-decoration: none; font-weight: 500;">Inicia sesión aquí</a></p>
        </div>
    </div>

    <!-- Script de Validaciones -->
    <script src="{{ asset('js/ScriptRegister.js') }}"></script>
</body>
</html>
