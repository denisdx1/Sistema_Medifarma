<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar Sesión</title>
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
            width: 420px;
            background: #ffffff;
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            border: 1px solid var(--muted-2);
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
            border: 2px dashed var(--muted-2);
        }

        /* Estilos para cuando la imagen no se carga */
        .logo::after {
            content: "Logo";
            display: block;
            text-align: center;
            color: var(--muted);
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
            color: #222222;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .text-muted {
            color: var(--muted) !important;
            margin-bottom: 32px;
        }

        .form-label {
            color: #333333;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .form-control {
            background: #ffffff;
            border: 2px solid var(--muted-2);
            color: #333333;
            padding: 12px 16px;
            border-radius: 8px;
            transition: all 0.15s ease;
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
        }

        .btn-primary {
            background: linear-gradient(90deg, var(--primary), var(--muted-2));
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-weight: 600;
            color: #fff;
            transition: all 0.15s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(90deg, #e0062b, var(--muted-2));
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(255,7,50,0.14);
        }

        .alert-danger {
            background: #fff0f0;
            border: 1px solid rgba(255,7,50,0.12);
            color: #7a0a12;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 24px;
        }

        .alert-success {
            background: #eff7ef;
            border: 1px solid #cfe9d6;
            color: #155724;
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

    @if ($errors->any())
        <div class="alert alert-danger">
            @foreach ($errors->all() as $error)
                {{ $error }}
            @endforeach
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('login.attempt') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Usuario</label>
            <input type="text" name="login" class="form-control" placeholder="Tu usuario de acceso" value="{{ old('login') }}" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Contraseña</label>
            <input type="password" name="password" class="form-control" placeholder="Tu contraseña" required>
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
        </div>
    </form>
</div>


</body>
</html>