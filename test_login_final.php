<?php

/**
 * Prueba de login para todos los usuarios con las contraseñas 123, 345, 567
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "=== PRUEBA DE LOGIN COMPLETA ===\n\n";

try {
    $authService = new \App\Services\AuthService();
    $usuarios = \App\Models\User::active()->get();
    $passwords = ['123', '345', '567'];
    
    echo "Usuarios disponibles: {$usuarios->count()}\n";
    echo "Contraseñas a probar: " . implode(', ', $passwords) . "\n\n";
    
    $credencialesCorrectas = [];
    
    foreach ($usuarios as $usuario) {
        echo "=== Usuario: {$usuario->usuario} (login: {$usuario->login}) ===\n";
        
        foreach ($passwords as $password) {
            echo "Probando password '{$password}'... ";
            
            // Verificar manualmente el hash
            $hashSHA256 = hash('sha256', $password, true);
            $esCorrecta = $hashSHA256 === $usuario->password;
            
            if ($esCorrecta) {
                echo "✅ CORRECTA!\n";
                $credencialesCorrectas[] = [
                    'usuario' => $usuario->usuario,
                    'login' => $usuario->login,
                    'password' => $password,
                    'rol' => $usuario->getRoleDisplayName()
                ];
                
                // Probar el AuthService
                echo "  Probando AuthService... ";
                $resultado = $authService->attempt([
                    'login' => $usuario->login,
                    'password' => $password
                ]);
                
                if ($resultado) {
                    echo "✅ AUTH OK\n";
                    // Hacer logout inmediatamente
                    $authService->logout();
                } else {
                    echo "❌ AUTH FALLÓ\n";
                }
                break; // Encontramos la contraseña, pasar al siguiente usuario
            } else {
                echo "❌ Incorrecta\n";
            }
        }
        echo "\n";
    }
    
    echo "=== RESUMEN DE CREDENCIALES CORRECTAS ===\n";
    if (empty($credencialesCorrectas)) {
        echo "❌ No se encontraron credenciales válidas\n";
    } else {
        foreach ($credencialesCorrectas as $cred) {
            echo "✅ {$cred['usuario']} ({$cred['rol']})\n";
            echo "   Login: {$cred['login']}\n";
            echo "   Password: {$cred['password']}\n";
            echo "   ---\n";
        }
    }
    
    echo "\n=== INSTRUCCIONES PARA EL FRONTEND ===\n";
    echo "1. Ve a http://localhost:8000/login\n";
    echo "2. Usa cualquiera de estas credenciales:\n\n";
    
    foreach ($credencialesCorrectas as $cred) {
        echo "👤 {$cred['rol']}:\n";
        echo "   Usuario: {$cred['login']}\n";
        echo "   Contraseña: {$cred['password']}\n\n";
    }
    
    echo "🎉 SISTEMA DE AUTENTICACIÓN LISTO!\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}
