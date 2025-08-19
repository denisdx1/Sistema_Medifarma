<?php

require_once 'vendor/autoload.php';

// Cargar configuración de Laravel
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    // Verificar estructura de la tabla ODS.TAB_SOLICITUD
    echo "=== Estructura de ODS.TAB_SOLICITUD ===\n";
    $columns = DB::connection('sqlsrv')->select("
        SELECT COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = 'ODS' AND TABLE_NAME = 'TAB_SOLICITUD'
        ORDER BY ORDINAL_POSITION
    ");
    
    foreach ($columns as $column) {
        echo "- {$column->COLUMN_NAME} ({$column->DATA_TYPE}) - Nullable: {$column->IS_NULLABLE}\n";
    }
    
    echo "\n=== Primeros 5 registros de ODS.TAB_SOLICITUD ===\n";
    $records = DB::connection('sqlsrv')->table('ODS.TAB_SOLICITUD')->take(5)->get();
    
    if ($records->count() > 0) {
        // Mostrar las columnas del primer registro
        $firstRecord = $records->first();
        foreach ($firstRecord as $key => $value) {
            echo "- {$key}: {$value}\n";
        }
    } else {
        echo "No hay registros en la tabla.\n";
    }
    
    echo "\n=== Mercados con JOIN a solicitud ===\n";
    $marketsWithSolicitud = DB::connection('sqlsrv')
        ->table('ODS.TAB_MERCADO as m')
        ->join('ODS.TAB_SOLICITUD as s', 'm.idSolicitud', '=', 's.idSolicitud')
        ->select('m.*', 's.solicitud')
        ->take(3)
        ->get();
    
    foreach ($marketsWithSolicitud as $market) {
        echo "- Mercado: {$market->mercado}, Solicitud: {$market->solicitud}\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
