<?php

require_once __DIR__ . '/vendor/autoload.php';

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

use Illuminate\Support\Facades\DB;

echo "=== VERIFICANDO DATOS DE SOLICITUDES ===\n";

try {
    // Verificar todos los valores en la tabla TAB_SOLICITUD
    $solicitudes = DB::connection('sqlsrv')
        ->table('ODS.TAB_SOLICITUD')
        ->select('idSolicitud', 'solicitud')
        ->get();
    
    echo "Valores en TAB_SOLICITUD:\n";
    foreach ($solicitudes as $solicitud) {
        echo "- ID: {$solicitud->idSolicitud}, Valor: '{$solicitud->solicitud}'\n";
    }
    
    echo "\n=== VERIFICANDO MERCADOS POR SOLICITUD ===\n";
    
    // Verificar cuántos mercados hay por cada tipo de solicitud
    $mercadosPorSolicitud = DB::connection('sqlsrv')
        ->table('ODS.TAB_MERCADO as m')
        ->join('ODS.TAB_SOLICITUD as s', 'm.idSolicitud', '=', 's.idSolicitud')
        ->select('s.solicitud', DB::raw('COUNT(*) as total'))
        ->groupBy('s.solicitud')
        ->get();
    
    echo "Conteo de mercados por solicitud:\n";
    foreach ($mercadosPorSolicitud as $item) {
        echo "- {$item->solicitud}: {$item->total} mercados\n";
    }
    
    echo "\n=== MERCADOS DENEGADOS (si existen) ===\n";
    
    // Buscar mercados específicamente denegados
    $mercadosDenegados = DB::connection('sqlsrv')
        ->table('ODS.TAB_MERCADO as m')
        ->join('ODS.TAB_SOLICITUD as s', 'm.idSolicitud', '=', 's.idSolicitud')
        ->where('s.solicitud', 'DENEGADO')
        ->select('m.idMercado', 'm.mercado', 's.solicitud', 'm.fechaRegistro')
        ->get();
    
    if ($mercadosDenegados->count() > 0) {
        foreach ($mercadosDenegados as $mercado) {
            echo "- ID: {$mercado->idMercado}, Nombre: {$mercado->mercado}, Estado: {$mercado->solicitud}, Fecha: {$mercado->fechaRegistro}\n";
        }
    } else {
        echo "No hay mercados denegados en la base de datos.\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== FIN VERIFICACIÓN ===\n";
