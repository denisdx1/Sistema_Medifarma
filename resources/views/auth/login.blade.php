<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar Sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #ffffff;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .card-login {
            width: 420px;
            background: #ffffff;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
            border: 1px solid #e9ecef;
        }
        
        .logo {
            display: block;
            margin: 0 auto 24px auto;
            max-width: 160px;
            height: auto;
            /* Fallback para cuando no se encuentra la imagen */
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            border: 2px dashed #dee2e6;
        }
        
        /* Estilos para cuando la imagen no se carga */
        .logo::after {
            content: "Logo";
            display: block;
            text-align: center;
            color: #6c757d;
            font-size: 14px;
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
            color: #343a40;
            font-weight: 600;
            margin-bottom: 8px;
        }
        
        .text-muted {
            color: #6c757d !important;
            margin-bottom: 32px;
        }
        
        .form-label {
            color: #495057;
            font-weight: 500;
            margin-bottom: 8px;
        }
        
        .form-control {
            background: #ffffff;
            border: 2px solid #e9ecef;
            color: #495057;
            padding: 12px 16px;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        
        .form-control:focus {
            background: #ffffff;
            border-color: #3760b8;
            box-shadow: 0 0 0 0.2rem rgba(55, 96, 184, 0.25);
            color: #495057;
        }
        
        .form-control::placeholder {
            color: #adb5bd;
        }
        
        .btn-primary {
            background: linear-gradient(90deg, #3760b8, #2e6fb9);
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s ease;
        }
        
        .btn-primary:hover {
            background: linear-gradient(90deg, #2e4a9b, #265a9c);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(55, 96, 184, 0.3);
        }
        
        .alert-danger {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 24px;
        }
        
        .mb-3 {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="card-login">
    <img src="{{ asset('images/logo-medifarma-Photoroom.png') }}" alt="Logo Medifarma" class="logo">

    <h3 class="text-center">Sistema de Gestión</h3>
    <p class="text-center text-muted">Ingresa tus credenciales para acceder</p>

    <!-- Simulación de error para demostración -->
    <div class="alert alert-danger" style="display: none;">
        Las credenciales proporcionadas no son válidas.
    </div>

    <form method="POST" action="{{ route('login.attempt') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Usuario</label>
            <input type="email" name="email" class="form-control" placeholder="Tu usuario" value="">
        </div>

        <div class="mb-3">
            <label class="form-label">Contraseña</label>
            <input type="password" name="password" class="form-control" placeholder="Tu contraseña">
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
        </div>
    </form>
</div>


</body>
</html>