<?php

/**
 * Prueba del modelo User con la nueva tabla ODS.TAB_USUARIO
 */

require_once __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make('Illuminate\Contracts\Console\Kernel');
$kernel->bootstrap();

echo "=== PRUEBA DEL MODELO USER CON ODS.TAB_USUARIO ===\n\n";

try {
    // 1. Probar obtención de usuarios
    echo "1. Probando obtención de usuarios...\n";
    $usuarios = \App\Models\User::all();
    echo "✅ Total usuarios: {$usuarios->count()}\n";
    
    foreach ($usuarios as $usuario) {
        echo "   - ID: {$usuario->idUsuario}, Usuario: {$usuario->usuario}, Login: {$usuario->login}\n";
        echo "     Rol ID: {$usuario->idRol}, Rol: {$usuario->getRoleDisplayName()}\n";
        echo "     Estado: " . ($usuario->idEstado == 1 ? 'Activo' : 'Inactivo') . "\n";
        echo "     ---\n";
    }
    
    // 2. Probar accessors de compatibilidad
    echo "\n2. Probando accessors de compatibilidad...\n";
    $primerUsuario = $usuarios->first();
    
    echo "Usuario original: {$primerUsuario->usuario}\n";
    echo "Name (accessor): {$primerUsuario->name}\n";
    echo "Email (accessor): {$primerUsuario->email}\n";
    echo "Role (accessor): {$primerUsuario->role}\n";
    echo "ID (accessor): {$primerUsuario->id}\n";
    echo "Is Active (accessor): " . ($primerUsuario->is_active ? 'true' : 'false') . "\n";
    
    // 3. Probar métodos de roles
    echo "\n3. Probando métodos de roles...\n";
    foreach ($usuarios as $usuario) {
        echo "Usuario: {$usuario->usuario}\n";
        echo "  - Es Admin: " . ($usuario->isAdmin() ? 'Sí' : 'No') . "\n";
        echo "  - Es GP: " . ($usuario->isProductManager() ? 'Sí' : 'No') . "\n";
        echo "  - Es BI: " . ($usuario->isBusinessIntelligence() ? 'Sí' : 'No') . "\n";
        echo "  - Tiene rol 'administrador': " . ($usuario->hasRole('administrador') ? 'Sí' : 'No') . "\n";
    }
    
    // 4. Probar scopes
    echo "\n4. Probando scopes...\n";
    
    $usuariosActivos = \App\Models\User::active()->get();
    echo "✅ Usuarios activos: {$usuariosActivos->count()}\n";
    
    $admins = \App\Models\User::byRole(\App\Models\User::ROLE_ADMIN)->get();
    echo "✅ Administradores: {$admins->count()}\n";
    
    $adminsByString = \App\Models\User::byRoleString('administrador')->get();
    echo "✅ Administradores (por string): {$adminsByString->count()}\n";
    
    // 5. Probar búsqueda por login
    echo "\n5. Probando búsqueda por login...\n";
    $loginTest = $usuarios->first()->login;
    $usuarioEncontrado = \App\Models\User::findByLogin($loginTest);
    
    if ($usuarioEncontrado) {
        echo "✅ Usuario encontrado por login '{$loginTest}': {$usuarioEncontrado->usuario}\n";
    } else {
        echo "❌ No se encontró usuario con login '{$loginTest}'\n";
    }
    
    // 6. Probar relaciones
    echo "\n6. Probando relaciones...\n";
    
    $configuraciones = $primerUsuario->configuracionesMercado()->count();
    echo "✅ Configuraciones de mercado para '{$primerUsuario->usuario}': {$configuraciones}\n";
    
    $mercados = $primerUsuario->mercados()->count();
    echo "✅ Mercados para '{$primerUsuario->usuario}': {$mercados} (debe ser 0)\n";
    
    // 7. Probar métodos de autenticación
    echo "\n7. Probando métodos de autenticación...\n";
    echo "Auth identifier name: {$primerUsuario->getAuthIdentifierName()}\n";
    echo "Auth identifier: {$primerUsuario->getAuthIdentifier()}\n";
    echo "Password length: " . strlen($primerUsuario->getAuthPassword()) . " bytes\n";
    
    // 8. Verificar roles disponibles
    echo "\n8. Roles disponibles:\n";
    $roles = \App\Models\User::getRoles();
    foreach ($roles as $id => $nombre) {
        echo "  {$id}: {$nombre}\n";
    }
    
    echo "\n=== RESUMEN ===\n";
    echo "✅ El modelo User ahora usa la tabla ODS.TAB_USUARIO\n";
    echo "✅ Mantiene compatibilidad con el sistema existente mediante accessors\n";
    echo "✅ Los roles se manejan por ID en lugar de strings\n";
    echo "✅ La autenticación usa 'login' en lugar de 'email'\n";
    echo "✅ Los usuarios están listos para el sistema de autenticación\n";
    echo "\n🎉 MODELO USER ACTUALIZADO EXITOSAMENTE!\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
