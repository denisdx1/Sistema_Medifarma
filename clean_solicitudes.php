<?php

require_once 'vendor/autoload.php';

// Cargar configuración de Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "=== Eliminando registro RECHAZADO ===\n";
    
    $deleted = DB::connection('sqlsrv')
        ->table('ODS.TAB_SOLICITUD')
        ->where('solicitud', 'RECHAZADO')
        ->delete();
    
    echo "✓ Registros eliminados: {$deleted}\n";
    
    echo "\n=== Verificando registros restantes ===\n";
    $solicitudes = DB::connection('sqlsrv')->table('ODS.TAB_SOLICITUD')->get();
    
    foreach ($solicitudes as $solicitud) {
        echo "- ID: {$solicitud->idSolicitud}, Solicitud: {$solicitud->solicitud}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
