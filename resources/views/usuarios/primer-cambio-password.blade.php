<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cambio de Contraseña - Sistema Medifarma</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style src="{{ asset('css/cambio-password.css') }}"></style>
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

        <div class="form-row">
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
