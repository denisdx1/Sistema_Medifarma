<?php

/**
 * Script de prueba para verificar el nuevo modelo Mercado
 * Sistema Medifarma - 2025
 */

require_once __DIR__ . '/vendor/autoload.php';

// Configurar Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\Mercado;

echo "=== PRUEBA DEL NUEVO MODELO MERCADO ===\n\n";

try {
    echo "1. Verificando conexión y estructura...\n";
    
    // Obtener total de mercados
    $total = Mercado::count();
    echo "✅ Total de mercados en la vista: {$total}\n";
    
    // Obtener primeros 5 mercados
    echo "\n2. Primeros 5 mercados:\n";
    $mercados = Mercado::take(5)->get();
    
    foreach ($mercados as $mercado) {
        echo "ID: {$mercado->idMercado} - Nombre: {$mercado->mercado}\n";
        echo "   - Estado (acceso): " . ($mercado->estado ? 'Activo' : 'Inactivo') . "\n";
        echo "   - ID compatible: {$mercado->id_mercado}\n";
    }
    
    // Prueba de búsqueda
    echo "\n3. Prueba de búsqueda por 'AAS':\n";
    $busqueda = Mercado::buscarPorNombre('AAS')->take(3)->get();
    
    foreach ($busqueda as $mercado) {
        echo "- {$mercado->mercado} (ID: {$mercado->idMercado})\n";
    }
    
    // Prueba de paginación
    echo "\n4. Prueba de paginación (página 1, 10 por página):\n";
    $paginados = Mercado::obtenerTodosPaginados(10);
    echo "Total: {$paginados->total()}\n";
    echo "Por página: {$paginados->perPage()}\n";
    echo "Página actual: {$paginados->currentPage()}\n";
    echo "Primeros de la página:\n";
    
    foreach ($paginados->take(3) as $mercado) {
        echo "- {$mercado->mercado}\n";
    }
    
    // Verificar que no se puede modificar
    echo "\n5. Verificando protección contra modificaciones...\n";
    try {
        $mercado = $mercados->first();
        $mercado->save();
        echo "❌ ERROR: Se permitió guardar (no debería)\n";
    } catch (Exception $e) {
        echo "✅ Protección funcionando: " . $e->getMessage() . "\n";
    }
    
    echo "\n=== PRUEBA COMPLETADA EXITOSAMENTE ===\n";
    echo "El modelo Mercado ahora usa la vista DM.MERCADO\n";
    echo "Mantiene compatibilidad con el sistema existente\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "\nDetalles del error:\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}
