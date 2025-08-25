<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cambio de Contraseña - Sistema Medifarma</title>
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
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

        .alert-warning {
            background: #fff8e1;
            border: 1px solid #ffcc02;
            color: #856404;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 24px;
        }

        .mb-3 {
            margin-bottom: 20px;
        }

        .password-requirements {
            background: #f8f9fa;
            border: 1px solid var(--muted-2);
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 20px;
            font-size: 14px;
            color: var(--muted);
        }

        .password-requirements ul {
            margin: 8px 0 0 0;
            padding-left: 20px;
        }

        .password-requirements li {
            margin-bottom: 4px;
        }
    </style>
</head>
<body>
<div class="card-login">
    <img src="<?php echo e(asset('images/logo-medifarma-Photoroom.png')); ?>" alt="Logo Medifarma" class="logo">

    <h3 class="text-center">Cambio de Contraseña</h3>
    <p class="text-center text-muted">Debe cambiar su contraseña temporal para continuar</p>

    <?php if(session('warning') || session('error') || session('success') || session('info')): ?>
        <div class="alert <?php echo e(session('warning') ? 'alert-warning' : (session('error') ? 'alert-danger' : 'alert-success')); ?>">
            <?php echo e(session('warning') ?? session('error') ?? session('success') ?? session('info')); ?>

        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="alert alert-danger">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php echo e($error); ?><br>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('usuarios.cambio-password.submit')); ?>" id="passwordForm">
        <?php echo csrf_field(); ?>
        
        <div class="mb-3">
            <label class="form-label">Contraseña Temporal</label>
            <input type="password" 
                   name="password_temporal" 
                   class="form-control <?php $__errorArgs = ['password_temporal'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                   placeholder="Ingrese su contraseña temporal" 
                   required>
            <?php $__errorArgs = ['password_temporal'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <div class="invalid-feedback"><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="mb-3">
            <label class="form-label">Nueva Contraseña</label>
            <input type="password" 
                   name="password" 
                   id="password"
                   class="form-control <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>" 
                   placeholder="Mínimo 6 caracteres" 
                   minlength="6"
                   required>
            <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                <div class="invalid-feedback"><?php echo e($message); ?></div>
            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
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
<?php /**PATH C:\Users\druizp\Documents\Sistema_Medifarma\resources\views/usuarios/primer-cambio-password.blade.php ENDPATH**/ ?>