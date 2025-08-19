<?php

/**
 * Prueba del sistema de autenticación completo con SHA2_256
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "=== PRUEBA DE AUTENTICACIÓN CON SHA2_256 ===\n\n";

try {
    // 1. Mostrar usuarios disponibles
    echo "1. Usuarios disponibles para login:\n";
    $usuarios = \App\Models\User::active()->get();
    
    foreach ($usuarios as $usuario) {
        echo "   - Login: {$usuario->login} | Usuario: {$usuario->usuario} | Rol: {$usuario->getRoleDisplayName()}\n";
    }
    
    // 2. Probar el AuthService directamente
    echo "\n2. Probando AuthService...\n";
    $authService = new \App\Services\AuthService();
    
    // Datos de prueba - DEBES CAMBIAR ESTOS POR CREDENCIALES REALES
    $credencialesPrueba = [
        'login' => 'vroqueh',
        'password' => 'password123' // CAMBIA ESTO POR LA CONTRASEÑA REAL
    ];
    
    echo "Intentando login con:\n";
    echo "  - Login: {$credencialesPrueba['login']}\n";
    echo "  - Password: [OCULTO]\n";
    
    // Buscar usuario
    $usuario = \App\Models\User::findByLogin($credencialesPrueba['login']);
    if (!$usuario) {
        echo "❌ Usuario no encontrado\n";
        exit;
    }
    
    echo "✅ Usuario encontrado: {$usuario->usuario}\n";
    
    // 3. Probar verificación de contraseña manual
    echo "\n3. Probando verificación de contraseña...\n";
    
    // Mostrar información sobre la contraseña almacenada
    echo "Password en BD (hex): " . bin2hex($usuario->password) . "\n";
    echo "Password en BD (length): " . strlen($usuario->password) . " bytes\n";
    
    // Probar diferentes contraseñas para encontrar la correcta
    $passwordsAPrueba = [
        '123',
        '345', 
        '567',
        'password123',
        'admin',
        '123456',
        'medifarma'
    ];
    
    echo "\nProbando contraseñas comunes:\n";
    foreach ($passwordsAPrueba as $passTest) {
        $hashSHA256 = hash('sha256', $passTest, true);
        $esCorrecta = $hashSHA256 === $usuario->password;
        
        echo "  - '{$passTest}': " . ($esCorrecta ? "✅ CORRECTA" : "❌ Incorrecta") . "\n";
        
        if ($esCorrecta) {
            echo "\n🎉 CONTRASEÑA ENCONTRADA: '{$passTest}'\n";
            
            // Probar el AuthService con la contraseña correcta
            echo "\n4. Probando AuthService con contraseña correcta...\n";
            $credencialesCorrectas = [
                'login' => $credencialesPrueba['login'],
                'password' => $passTest
            ];
            
            $resultado = $authService->attempt($credencialesCorrectas);
            if ($resultado) {
                echo "✅ AUTENTICACIÓN EXITOSA!\n";
                echo "Usuario autenticado: " . \Illuminate\Support\Facades\Auth::user()->usuario . "\n";
                echo "Rol: " . \Illuminate\Support\Facades\Auth::user()->getRoleDisplayName() . "\n";
                
                // Logout
                $authService->logout();
                echo "✅ Logout exitoso\n";
            } else {
                echo "❌ Error en autenticación\n";
            }
            break;
        }
    }
    
    // 5. Información adicional
    echo "\n5. Información para configurar el frontend:\n";
    echo "Campo de login: 'login' (no 'email')\n";
    echo "Campo de password: 'password'\n";
    echo "Cifrado: SHA2_256\n";
    echo "Usuarios disponibles:\n";
    foreach ($usuarios as $u) {
        echo "  - {$u->login} ({$u->usuario})\n";
    }
    
    echo "\n=== PRUEBA COMPLETADA ===\n";
    echo "Para probar en el navegador:\n";
    echo "1. Ve a /login\n";
    echo "2. Usa login: vroqueh\n";
    echo "3. Usa la contraseña que se mostró como correcta arriba\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}
