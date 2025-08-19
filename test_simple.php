<?php

/**
 * Prueba simple del modelo Mercado
 */

require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

// Cargar .env
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "=== PRUEBA DIRECTA DE CONEXIÓN Y MODELO ===\n\n";

try {
    // Probar conexión directa primero
    echo "1. Probando conexión directa...\n";
    $dsn = "sqlsrv:Server={$_ENV['DB_HOST']},{$_ENV['DB_PORT']};Database={$_ENV['DB_DATABASE']}";
    $pdo = new PDO($dsn, $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query("SELECT TOP 3 idMercado, mercado FROM DM.MERCADO ORDER BY mercado");
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "✅ Conexión directa exitosa!\n";
    echo "Primeros 3 mercados desde DM.MERCADO:\n";
    foreach ($resultados as $mercado) {
        echo "- ID: {$mercado['idMercado']}, Nombre: {$mercado['mercado']}\n";
    }
    
    // Ahora configurar Laravel manualmente
    echo "\n2. Configurando Laravel...\n";
    
    $app = require_once __DIR__ . '/bootstrap/app.php';
    $kernel = $app->make('Illuminate\Contracts\Console\Kernel');
    $kernel->bootstrap();
    
    // Probar el modelo
    echo "\n3. Probando modelo Mercado...\n";
    
    $totalMercados = \App\Models\Mercado::count();
    echo "✅ Total mercados desde modelo: {$totalMercados}\n";
    
    $primerosTres = \App\Models\Mercado::take(3)->get();
    echo "Primeros 3 desde modelo:\n";
    foreach ($primerosTres as $mercado) {
        echo "- ID: {$mercado->idMercado}, Nombre: {$mercado->mercado}\n";
        echo "  Compatibilidad - id_mercado: {$mercado->id_mercado}, estado: " . ($mercado->estado ? 'true' : 'false') . "\n";
    }
    
    echo "\n✅ TODO FUNCIONANDO CORRECTAMENTE!\n";
    echo "El modelo Mercado ahora usa la vista DM.MERCADO y mantiene compatibilidad.\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Archivo: " . $e->getFile() . "\n";
    echo "Línea: " . $e->getLine() . "\n";
}
