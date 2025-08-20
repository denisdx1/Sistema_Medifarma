<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

// Script para debuggear el problema con $request
echo "=== DEBUGGING MARKET ADMINISTRATION ===\n";

// Verificar conexión a base de datos
try {
    $connection = DB::connection('sqlsrv');
    echo "✓ Conexión a base de datos OK\n";
} catch (Exception $e) {
    echo "✗ Error de conexión: " . $e->getMessage() . "\n";
    exit;
}

// Verificar tablas necesarias
try {
    $mercados = DB::connection('sqlsrv')->table('ODS.TAB_MERCADO')->count();
    echo "✓ Tabla TAB_MERCADO existe - Total mercados: $mercados\n";
    
    $solicitudes = DB::connection('sqlsrv')->table('ODS.TAB_SOLICITUD')->count();
    echo "✓ Tabla TAB_SOLICITUD existe - Total solicitudes: $solicitudes\n";
    
    $estados = DB::connection('sqlsrv')->table('ODS.TAB_ESTADO')->count();
    echo "✓ Tabla TAB_ESTADO existe - Total estados: $estados\n";
} catch (Exception $e) {
    echo "✗ Error verificando tablas: " . $e->getMessage() . "\n";
}

// Probar query básica
try {
    $query = DB::connection('sqlsrv')
        ->table('ODS.TAB_MERCADO as m')
        ->join('ODS.TAB_SOLICITUD as s', 'm.idSolicitud', '=', 's.idSolicitud')
        ->join('ODS.TAB_ESTADO as e', 'm.idEstado', '=', 'e.idEstado')
        ->select('m.*', 's.solicitud', 'e.estado')
        ->limit(5)
        ->get();
    
    echo "✓ Query de mercados ejecutada correctamente - " . $query->count() . " resultados\n";
} catch (Exception $e) {
    echo "✗ Error en query de mercados: " . $e->getMessage() . "\n";
}

echo "\n=== FIN DEBUG ===\n";
