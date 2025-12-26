<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cambio de Contraseña - Sistema Medifarma</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        :root{
            --primary: #ff0732;   /* rojo principal */
            --muted: #949697;    /* gris medio */
            --muted-2: #bcbdbf;  /* gris claro */
        }

        body {
            background: #ffffff;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .card-login {
            width: 360px;
            background: #ffffff;
            border-radius: 12px;
            padding: 28px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--muted-2);
        }

        .logo {
            display: block;
            margin: 0 auto 18px auto;
            max-width: 120px;
            height: auto;
            /* Fallback para cuando no se encuentra la imagen */
            background: #f8f9fa;
            border-radius: 6px;
            padding: 15px;
            border: 2px dashed var(--muted-2);
        }

        /* Estilos para cuando la imagen no se carga */
        .logo::after {
            content: "Logo";
            display: block;
            text-align: center;
            color: var(--muted);
            font-size: 12px;
            font-weight: 500;
        }

        /* Cuando la imagen se carga correctamente, ocultar el texto */
        .logo[src] {
            background: transparent;
            border: none;
            padding: 0;
        }

        .logo[src]::after {
            display: none;
        }

        h3 {
            color: #222222;
            font-weight: 600;
            margin-bottom: 6px;
            font-size: 20px;
        }

        .text-muted {
            color: var(--muted) !important;
            margin-bottom: 22px;
            font-size: 13px;
        }

        .form-label {
            color: #333333;
            font-weight: 500;
            margin-bottom: 6px;
            font-size: 13px;
        }

        .form-control {
            background: #ffffff;
            border: 2px solid var(--muted-2);
            color: #333333;
            padding: 10px 12px;
            border-radius: 6px;
            transition: all 0.15s ease;
            font-size: 14px;
        }

        .form-control:focus {
            background: #ffffff;
            border-color: var(--primary);
            box-shadow: 0 0 0 0.12rem rgba(255,7,50,0.12);
            color: #333333;
        }

        .form-control::placeholder {
            color: var(--muted);
            opacity: 1;
            font-size: 13px;
        }

        .form-control.is-invalid {
            border-color: var(--primary);
            background: #fff0f0;
        }

        .invalid-feedback {
            color: var(--primary);
            font-size: 11px;
            margin-top: 4px;
        }

        .btn-primary {
            background: linear-gradient(90deg, var(--primary), var(--muted-2));
            border: none;
            padding: 10px;
            border-radius: 6px;
            font-weight: 600;
            color: #fff;
            transition: all 0.15s ease;
            font-size: 14px;
        }

        .btn-primary:hover {
            background: linear-gradient(90deg, #e0062b, var(--muted-2));
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(255,7,50,0.14);
        }

        .alert-danger {
            background: #fff0f0;
            border: 1px solid rgba(255,7,50,0.12);
            color: #7a0a12;
            border-radius: 6px;
            padding: 10px 12px;
            margin-bottom: 18px;
            font-size: 12px;
        }

        .alert-success {
            background: #eff7ef;
            border: 1px solid #cfe9d6;
            color: #155724;
            border-radius: 6px;
            padding: 10px 12px;
            margin-bottom: 18px;
            font-size: 12px;
        }

        .alert-warning {
            background: #fff8e1;
            border: 1px solid #ffe082;
            color: #856404;
            border-radius: 6px;
            padding: 10px 12px;
            margin-bottom: 18px;
            font-size: 12px;
        }

        .alert-info {
            background: #e3f2fd;
            border: 1px solid #bbdefb;
            color: #0c5460;
            border-radius: 6px;
            padding: 10px 12px;
            margin-bottom: 18px;
            font-size: 12px;
        }

        .mb-3 {
            margin-bottom: 16px;
        }

        .password-requirements {
            background: #f8f9fa;
            border: 1px solid var(--muted-2);
            border-radius: 6px;
            padding: 12px;
            margin-bottom: 18px;
        }

        .password-requirements strong {
            color: #333333;
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
            font-size: 12px;
        }

        .password-requirements ul {
            margin: 0;
            padding-left: 16px;
        }

        .password-requirements li {
            color: var(--muted);
            margin-bottom: 4px;
            font-size: 11px;
        }

        .password-requirements li:last-child {
            margin-bottom: 0;
        }

        /* Responsive para pantallas muy pequeñas */
        @media (max-width: 400px) {
            .card-login {
                width: 320px;
                padding: 24px;
                margin: 10px;
            }
            
            h3 {
                font-size: 18px;
            }
            
            .text-muted {
                font-size: 12px;
            }
            
            .form-label {
                font-size: 12px;
            }
            
            .form-control {
                padding: 8px 10px;
                font-size: 13px;
            }
            
            .btn-primary {
                padding: 8px;
                font-size: 13px;
            }
        }
    </style>
</head>
<body>
<div class="card-login">
    <img src="{{ asset('images/logo-medifarma-Photoroom.png') }}" alt="Logo Medifarma" class="logo">

    <h3 class="text-center">Cambio de Contraseña</h3>
    <p class="text-center text-muted">Debe cambiar su contraseña temporal para continuar</p>

    @if(session('warning') || session('error') || session('success') || session('info'))
        <div class="alert {{ session('warning') ? 'alert-warning' : (session('error') ? 'alert-danger' : 'alert-success') }}">
            {{ session('warning') ?? session('error') ?? session('success') ?? session('info') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                {{ $error }}<br>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('usuarios.cambio-password.submit') }}" id="passwordForm">
        @csrf
        
        <div class="mb-3">
            <label class="form-label">Contraseña Temporal</label>
            <input type="password" 
                   name="password_temporal" 
                   class="form-control @error('password_temporal') is-invalid @enderror" 
                   placeholder="Ingrese su contraseña temporal" 
                   required>
            @error('password_temporal')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Nueva Contraseña</label>
            <input type="password" 
                   name="password" 
                   id="password"
                   class="form-control @error('password') is-invalid @enderror" 
                   placeholder="Mínimo 6 caracteres" 
                   minlength="6"
                   required>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label">Confirmar Nueva Contraseña</label>
            <input type="password" 
                   name="password_confirmation" 
                   id="password_confirmation"
                   class="form-control" 
                   placeholder="Repita la nueva contraseña" 
                   minlength="6"
                   required>
        </div>

        <div class="password-requirements">
            <strong>Requisitos de seguridad:</strong>
            <ul>
                <li>Mínimo 6 caracteres</li>
                <li>Diferente de la contraseña temporal</li>
                <li>Fácil de recordar para usted</li>
            </ul>
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-primary">Cambiar Contraseña y Continuar</button>
        </div>
    </form>
</div>

<!-- Scripts -->
<script>
    // Auto-focus on first field
    document.addEventListener('DOMContentLoaded', function() {
        const firstField = document.querySelector('input[name="password_temporal"]');
        if (firstField) {
            firstField.focus();
        }
    });

    // Form validation
    document.getElementById('passwordForm').addEventListener('submit', function(e) {
        const password = document.getElementById('password').value;
        const confirmation = document.getElementById('password_confirmation').value;
        const temporal = document.querySelector('input[name="password_temporal"]').value;
        
        if (password !== confirmation) {
            e.preventDefault();
            alert('Las contraseñas no coinciden');
            return;
        }
        
        if (password === temporal) {
            e.preventDefault();
            alert('La nueva contraseña debe ser diferente de la temporal');
            return;
        }

        if (password.length < 6) {
            e.preventDefault();
            alert('La contraseña debe tener al menos 6 caracteres');
            return;
        }
    });
</script>

</body>
</html>
