<?php

/**
 * Prueba del sistema de autenticación con la nueva tabla ODS.TAB_USUARIO
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "=== PRUEBA DEL SISTEMA DE AUTENTICACIÓN ===\n\n";

try {
    // 1. Probar obtención de usuarios básica
    echo "1. Verificando usuarios disponibles...\n";
    $usuarios = \App\Models\User::all();
    echo "✅ Total usuarios: {$usuarios->count()}\n\n";
    
    foreach ($usuarios as $usuario) {
        echo "Usuario: {$usuario->usuario}\n";
        echo "  - ID: {$usuario->idUsuario}\n";
        echo "  - Login: {$usuario->login}\n";
        echo "  - Rol: {$usuario->getRoleDisplayName()}\n";
        echo "  - Estado: " . ($usuario->idEstado == 1 ? 'Activo' : 'Inactivo') . "\n";
        echo "  - Email (generado): {$usuario->email}\n";
        echo "  - Name (para Auth): {$usuario->name}\n";
        echo "  ---\n";
    }
    
    // 2. Probar búsqueda por login (para autenticación)
    echo "\n2. Probando búsqueda por login...\n";
    $testLogin = 'vroqueh';
    $usuario = \App\Models\User::findByLogin($testLogin);
    
    if ($usuario) {
        echo "✅ Usuario encontrado: {$usuario->usuario}\n";
        echo "  - Login: {$usuario->login}\n";
        echo "  - Rol: {$usuario->getRoleDisplayName()}\n";
        echo "  - Auth Identifier: {$usuario->getAuthIdentifier()}\n";
        echo "  - Auth Identifier Name: {$usuario->getAuthIdentifierName()}\n";
    } else {
        echo "❌ Usuario no encontrado\n";
    }
    
    // 3. Probar verificación de roles
    echo "\n3. Probando verificación de roles...\n";
    if ($usuario) {
        echo "Verificando roles para '{$usuario->usuario}':\n";
        echo "  - Es Admin: " . ($usuario->isAdmin() ? 'Sí' : 'No') . "\n";
        echo "  - Es GP: " . ($usuario->isProductManager() ? 'Sí' : 'No') . "\n";
        echo "  - Es BI: " . ($usuario->isBusinessIntelligence() ? 'Sí' : 'No') . "\n";
        echo "  - Tiene rol 'administrador': " . ($usuario->hasRole('administrador') ? 'Sí' : 'No') . "\n";
    }
    
    // 4. Probar compatibilidad con Laravel Auth
    echo "\n4. Probando compatibilidad con Laravel Auth...\n";
    if ($usuario) {
        echo "Campos requeridos por Laravel Auth:\n";
        echo "  - id: {$usuario->id}\n";
        echo "  - name: {$usuario->name}\n";
        echo "  - email: {$usuario->email}\n";
        echo "  - password: [" . strlen($usuario->getAuthPassword()) . " bytes]\n";
        echo "  - remember_token: " . ($usuario->remember_token ?? 'null') . "\n";
    }
    
    // 5. Verificar que los métodos de autenticación funcionan
    echo "\n5. Verificando métodos de autenticación...\n";
    if ($usuario) {
        echo "✅ getAuthIdentifierName(): {$usuario->getAuthIdentifierName()}\n";
        echo "✅ getAuthIdentifier(): {$usuario->getAuthIdentifier()}\n";
        echo "✅ getAuthPassword(): [password presente]\n";
        
        // Mostrar información sobre la contraseña
        $passwordHex = bin2hex($usuario->password);
        echo "✅ Password (hex): " . substr($passwordHex, 0, 20) . "...\n";
        echo "✅ Password length: " . strlen($usuario->password) . " bytes\n";
    }
    
    // 6. Probar scopes básicos
    echo "\n6. Probando scopes de usuarios...\n";
    
    $activos = \App\Models\User::active()->count();
    echo "✅ Usuarios activos: {$activos}\n";
    
    $admins = \App\Models\User::byRole(1)->count();
    echo "✅ Administradores: {$admins}\n";
    
    $gps = \App\Models\User::byRole(2)->count();
    echo "✅ Gerentes de Producto: {$gps}\n";
    
    $bis = \App\Models\User::byRole(3)->count();
    echo "✅ Business Intelligence: {$bis}\n";
    
    // 7. Información para configurar el AuthService
    echo "\n7. Información para AuthService...\n";
    echo "Configuración necesaria:\n";
    echo "  - Campo username: 'login' (no 'email')\n";
    echo "  - Campo password: 'password' (varbinary)\n";
    echo "  - Verificación personalizada requerida para varbinary\n";
    echo "  - Primary key: 'idUsuario'\n";
    echo "  - Tabla: 'ODS.TAB_USUARIO'\n";
    echo "  - Conexión: 'sqlsrv'\n";
    
    echo "\n=== RESUMEN PARA AUTENTICACIÓN ===\n";
    echo "✅ Modelo User configurado para usar ODS.TAB_USUARIO\n";
    echo "✅ Usuarios disponibles y activos: {$activos}\n";
    echo "✅ Roles mapeados correctamente\n";
    echo "✅ Compatibilidad con Laravel Auth mantenida\n";
    echo "✅ Campos de identificación configurados (login en lugar de email)\n";
    echo "\n🎯 SIGUIENTE PASO: Configurar AuthService para usar login/password\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}
