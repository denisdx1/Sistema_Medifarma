<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "\n=== VERIFICANDO STORED PROCEDURE ODS.SP_UPDATE_CONFIGURACION ===\n\n";
    
    // Consulta para ver los parámetros del SP
    $parameters = DB::select("
        SELECT 
            p.name AS parameter_name,
            p.parameter_id,
            TYPE_NAME(p.user_type_id) AS parameter_type,
            p.max_length,
            p.is_output,
            p.has_default_value
        FROM sys.procedures pr
        INNER JOIN sys.parameters p ON pr.object_id = p.object_id
        WHERE pr.name = 'SP_UPDATE_CONFIGURACION'
        AND SCHEMA_NAME(pr.schema_id) = 'ODS'
        ORDER BY p.parameter_id
    ");
    
    if (empty($parameters)) {
        echo "❌ No se encontró el stored procedure ODS.SP_UPDATE_CONFIGURACION\n";
        
        // Buscar SPs similares
        $similar = DB::select("
            SELECT 
                SCHEMA_NAME(pr.schema_id) AS schema_name,
                pr.name AS procedure_name
            FROM sys.procedures pr
            WHERE pr.name LIKE '%UPDATE%CONFIGURACION%'
            OR pr.name LIKE '%CONFIGURACION%'
        ");
        
        if (!empty($similar)) {
            echo "\n📋 Stored procedures similares encontrados:\n";
            foreach ($similar as $sp) {
                echo "  - {$sp->schema_name}.{$sp->procedure_name}\n";
            }
        }
    } else {
        echo "✅ Stored procedure encontrado!\n\n";
        echo "📋 PARÁMETROS:\n";
        foreach ($parameters as $param) {
            $direction = $param->is_output ? 'OUTPUT' : 'INPUT';
            $hasDefault = $param->has_default_value ? '(con valor por defecto)' : '';
            echo "  {$param->parameter_name}\n";
            echo "    - Tipo: {$param->parameter_type}\n";
            echo "    - Dirección: {$direction}\n";
            echo "    - Longitud máxima: {$param->max_length} {$hasDefault}\n\n";
        }
    }
    
    // También buscar la definición del SP
    echo "\n🔍 DEFINICIÓN DEL STORED PROCEDURE:\n";
    $definition = DB::select("
        SELECT OBJECT_DEFINITION(OBJECT_ID('ODS.SP_UPDATE_CONFIGURACION')) AS definition
    ");
    
    if (!empty($definition) && $definition[0]->definition) {
        $def = $definition[0]->definition;
        // Mostrar solo los primeros 2000 caracteres para no saturar
        if (strlen($def) > 2000) {
            echo substr($def, 0, 2000) . "\n...(truncado)\n";
        } else {
            echo $def . "\n";
        }
    } else {
        echo "❌ No se pudo obtener la definición del SP\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace: " . $e->getTraceAsString() . "\n";
}

echo "\n=== FIN DE LA VERIFICACIÓN ===\n";
