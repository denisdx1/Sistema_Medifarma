<?php

require_once 'vendor/autoload.php';

// Cargar configuración de Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "=== Verificando registros en ODS.TAB_SOLICITUD ===\n";
    $solicitudes = DB::connection('sqlsrv')->table('ODS.TAB_SOLICITUD')->get();
    
    foreach ($solicitudes as $solicitud) {
        echo "- ID: {$solicitud->idSolicitud}, Solicitud: {$solicitud->solicitud}\n";
    }
    
    // Verificar si existen los registros necesarios
    $espera = DB::connection('sqlsrv')->table('ODS.TAB_SOLICITUD')->where('solicitud', 'ESPERA')->first();
    $aprobado = DB::connection('sqlsrv')->table('ODS.TAB_SOLICITUD')->where('solicitud', 'APROBADO')->first();
    $rechazado = DB::connection('sqlsrv')->table('ODS.TAB_SOLICITUD')->where('solicitud', 'RECHAZADO')->first();
    
    echo "\n=== Estados necesarios ===\n";
    echo "ESPERA: " . ($espera ? "✓ ID: {$espera->idSolicitud}" : "❌ NO EXISTE") . "\n";
    echo "APROBADO: " . ($aprobado ? "✓ ID: {$aprobado->idSolicitud}" : "❌ NO EXISTE") . "\n";
    echo "RECHAZADO: " . ($rechazado ? "✓ ID: {$rechazado->idSolicitud}" : "❌ NO EXISTE") . "\n";
    
    // Si no existe RECHAZADO, crearlo
    if (!$rechazado) {
        echo "\n=== Creando registro RECHAZADO ===\n";
        
        DB::connection('sqlsrv')->table('ODS.TAB_SOLICITUD')->insert([
            'solicitud' => 'RECHAZADO'
        ]);
        
        echo "✓ Creado registro RECHAZADO\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
