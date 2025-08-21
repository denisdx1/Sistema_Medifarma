<?php
// Explorar estructura del SP_ASIGNAR_RESTO
require_once 'vendor/autoload.php';

try {
    // Configuración de conexión a SQL Server
    $serverName = "localhost";
    $connectionInfo = array(
        "Database" => "SISTEMA_MEDIFARMA",
        "Uid" => "sa",
        "PWD" => "Temporal123*",
        "CharacterSet" => "UTF-8"
    );

    $conn = sqlsrv_connect($serverName, $connectionInfo);
    
    if ($conn === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    echo "<h1>🔍 Exploración del SP_ASIGNAR_RESTO</h1>";
    echo "<hr>";

    // 1. Verificar si el SP existe
    echo "<h2>1. Verificar existencia del SP</h2>";
    $sqlCheck = "
        SELECT 
            ROUTINE_NAME,
            ROUTINE_TYPE,
            ROUTINE_DEFINITION
        FROM INFORMATION_SCHEMA.ROUTINES 
        WHERE ROUTINE_NAME = 'SP_ASIGNAR_RESTO'
        AND ROUTINE_SCHEMA = 'ODS'
    ";
    
    $stmtCheck = sqlsrv_query($conn, $sqlCheck);
    if ($stmtCheck === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $spExists = false;
    while ($row = sqlsrv_fetch_array($stmtCheck, SQLSRV_FETCH_ASSOC)) {
        $spExists = true;
        echo "<div style='background: #e8f5e8; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>✅ SP Encontrado:</strong><br>";
        echo "<strong>Nombre:</strong> " . $row['ROUTINE_NAME'] . "<br>";
        echo "<strong>Tipo:</strong> " . $row['ROUTINE_TYPE'] . "<br>";
        echo "</div>";
    }

    if (!$spExists) {
        echo "<div style='background: #ffe8e8; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>❌ SP no encontrado en ODS schema</strong>";
        echo "</div>";
        
        // Buscar en otros schemas
        echo "<h3>🔍 Buscando en todos los schemas...</h3>";
        $sqlSearchAll = "
            SELECT 
                ROUTINE_SCHEMA,
                ROUTINE_NAME,
                ROUTINE_TYPE
            FROM INFORMATION_SCHEMA.ROUTINES 
            WHERE ROUTINE_NAME LIKE '%ASIGNAR_RESTO%'
        ";
        
        $stmtSearchAll = sqlsrv_query($conn, $sqlSearchAll);
        while ($row = sqlsrv_fetch_array($stmtSearchAll, SQLSRV_FETCH_ASSOC)) {
            echo "<div style='background: #e8f0ff; padding: 10px; border-radius: 5px; margin: 5px 0;'>";
            echo "<strong>📍 Encontrado:</strong> " . $row['ROUTINE_SCHEMA'] . "." . $row['ROUTINE_NAME'] . " (" . $row['ROUTINE_TYPE'] . ")";
            echo "</div>";
        }
    }

    // 2. Obtener parámetros del SP
    echo "<h2>2. Parámetros del SP</h2>";
    $sqlParams = "
        SELECT 
            PARAMETER_NAME,
            PARAMETER_MODE,
            DATA_TYPE,
            CHARACTER_MAXIMUM_LENGTH,
            NUMERIC_PRECISION,
            NUMERIC_SCALE,
            IS_RESULT
        FROM INFORMATION_SCHEMA.PARAMETERS 
        WHERE SPECIFIC_NAME = 'SP_ASIGNAR_RESTO'
        AND SPECIFIC_SCHEMA = 'ODS'
        ORDER BY ORDINAL_POSITION
    ";
    
    $stmtParams = sqlsrv_query($conn, $sqlParams);
    if ($stmtParams === false) {
        die(print_r(sqlsrv_errors(), true));
    }

    $hasParams = false;
    echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
    echo "<tr style='background: #f0f0f0;'>";
    echo "<th>Parámetro</th><th>Modo</th><th>Tipo</th><th>Longitud</th><th>Precisión</th><th>Escala</th>";
    echo "</tr>";
    
    while ($row = sqlsrv_fetch_array($stmtParams, SQLSRV_FETCH_ASSOC)) {
        $hasParams = true;
        echo "<tr>";
        echo "<td><strong>" . ($row['PARAMETER_NAME'] ?: 'N/A') . "</strong></td>";
        echo "<td>" . ($row['PARAMETER_MODE'] ?: 'N/A') . "</td>";
        echo "<td>" . ($row['DATA_TYPE'] ?: 'N/A') . "</td>";
        echo "<td>" . ($row['CHARACTER_MAXIMUM_LENGTH'] ?: 'N/A') . "</td>";
        echo "<td>" . ($row['NUMERIC_PRECISION'] ?: 'N/A') . "</td>";
        echo "<td>" . ($row['NUMERIC_SCALE'] ?: 'N/A') . "</td>";
        echo "</tr>";
    }
    echo "</table>";

    if (!$hasParams) {
        echo "<div style='background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>⚠️ No se encontraron parámetros en INFORMATION_SCHEMA</strong>";
        echo "</div>";
    }

    // 3. Buscar el SP en sys.objects y sys.procedures
    echo "<h2>3. Búsqueda en sys.objects</h2>";
    $sqlSysObjects = "
        SELECT 
            s.name as schema_name,
            o.name as object_name,
            o.type_desc,
            o.create_date,
            o.modify_date
        FROM sys.objects o
        INNER JOIN sys.schemas s ON o.schema_id = s.schema_id
        WHERE o.name LIKE '%ASIGNAR_RESTO%'
        AND o.type IN ('P', 'PC')
    ";
    
    $stmtSysObjects = sqlsrv_query($conn, $sqlSysObjects);
    while ($row = sqlsrv_fetch_array($stmtSysObjects, SQLSRV_FETCH_ASSOC)) {
        echo "<div style='background: #e8f5e8; padding: 10px; border-radius: 5px; margin: 5px 0;'>";
        echo "<strong>📍 SP en sys.objects:</strong><br>";
        echo "<strong>Schema:</strong> " . $row['schema_name'] . "<br>";
        echo "<strong>Nombre:</strong> " . $row['object_name'] . "<br>";
        echo "<strong>Tipo:</strong> " . $row['type_desc'] . "<br>";
        echo "<strong>Creado:</strong> " . $row['create_date']->format('Y-m-d H:i:s') . "<br>";
        echo "<strong>Modificado:</strong> " . $row['modify_date']->format('Y-m-d H:i:s') . "<br>";
        echo "</div>";
    }

    // 4. Obtener parámetros usando sys.parameters
    echo "<h2>4. Parámetros usando sys.parameters</h2>";
    $sqlSysParams = "
        SELECT 
            p.name as parameter_name,
            t.name as data_type,
            p.max_length,
            p.precision,
            p.scale,
            p.is_output,
            p.parameter_id
        FROM sys.parameters p
        INNER JOIN sys.types t ON p.user_type_id = t.user_type_id
        INNER JOIN sys.objects o ON p.object_id = o.object_id
        INNER JOIN sys.schemas s ON o.schema_id = s.schema_id
        WHERE o.name LIKE '%ASIGNAR_RESTO%'
        AND s.name = 'ODS'
        ORDER BY p.parameter_id
    ";
    
    $stmtSysParams = sqlsrv_query($conn, $sqlSysParams);
    if ($stmtSysParams === false) {
        echo "<div style='background: #ffe8e8; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>❌ Error al consultar sys.parameters:</strong><br>";
        echo print_r(sqlsrv_errors(), true);
        echo "</div>";
    } else {
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th>ID</th><th>Parámetro</th><th>Tipo</th><th>Longitud</th><th>Precisión</th><th>Escala</th><th>Output</th>";
        echo "</tr>";
        
        $paramsFound = false;
        while ($row = sqlsrv_fetch_array($stmtSysParams, SQLSRV_FETCH_ASSOC)) {
            $paramsFound = true;
            echo "<tr>";
            echo "<td>" . $row['parameter_id'] . "</td>";
            echo "<td><strong>" . ($row['parameter_name'] ?: '@RETURN_VALUE') . "</strong></td>";
            echo "<td>" . $row['data_type'] . "</td>";
            echo "<td>" . $row['max_length'] . "</td>";
            echo "<td>" . $row['precision'] . "</td>";
            echo "<td>" . $row['scale'] . "</td>";
            echo "<td>" . ($row['is_output'] ? 'SÍ' : 'NO') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        
        if (!$paramsFound) {
            echo "<div style='background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
            echo "<strong>⚠️ No se encontraron parámetros en sys.parameters para ODS.SP_ASIGNAR_RESTO</strong>";
            echo "</div>";
        }
    }

    // 5. Intentar obtener la definición del SP
    echo "<h2>5. Definición del SP</h2>";
    $sqlDefinition = "
        SELECT OBJECT_DEFINITION(OBJECT_ID('ODS.SP_ASIGNAR_RESTO')) AS definition
    ";
    
    $stmtDefinition = sqlsrv_query($conn, $sqlDefinition);
    if ($stmtDefinition === false) {
        echo "<div style='background: #ffe8e8; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>❌ Error al obtener definición:</strong><br>";
        echo print_r(sqlsrv_errors(), true);
        echo "</div>";
    } else {
        while ($row = sqlsrv_fetch_array($stmtDefinition, SQLSRV_FETCH_ASSOC)) {
            if ($row['definition']) {
                echo "<div style='background: #f8f9fa; padding: 15px; border: 1px solid #dee2e6; border-radius: 5px; margin: 10px 0;'>";
                echo "<strong>📝 Definición del SP:</strong><br>";
                echo "<pre style='white-space: pre-wrap; font-family: monospace; font-size: 12px; max-height: 400px; overflow-y: auto;'>";
                echo htmlspecialchars($row['definition']);
                echo "</pre>";
                echo "</div>";
            } else {
                echo "<div style='background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
                echo "<strong>⚠️ No se pudo obtener la definición del SP</strong>";
                echo "</div>";
            }
        }
    }

    // 6. Buscar SPs similares que puedan dar pistas
    echo "<h2>6. SPs relacionados</h2>";
    $sqlRelated = "
        SELECT 
            s.name as schema_name,
            o.name as object_name,
            o.type_desc
        FROM sys.objects o
        INNER JOIN sys.schemas s ON o.schema_id = s.schema_id
        WHERE o.name LIKE '%ASIGNAR%' OR o.name LIKE '%RESTO%'
        AND o.type IN ('P', 'PC')
        ORDER BY s.name, o.name
    ";
    
    $stmtRelated = sqlsrv_query($conn, $sqlRelated);
    echo "<ul>";
    while ($row = sqlsrv_fetch_array($stmtRelated, SQLSRV_FETCH_ASSOC)) {
        echo "<li><strong>" . $row['schema_name'] . "." . $row['object_name'] . "</strong> (" . $row['type_desc'] . ")</li>";
    }
    echo "</ul>";

    sqlsrv_close($conn);

} catch (Exception $e) {
    echo "<div style='background: #ffe8e8; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
    echo "<strong>❌ Error:</strong> " . $e->getMessage();
    echo "</div>";
}
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    h1 { color: #333; }
    h2 { color: #666; border-bottom: 2px solid #eee; padding-bottom: 5px; }
    table { border-collapse: collapse; width: 100%; }
    th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
    th { background-color: #f2f2f2; }
    pre { background: #f8f9fa; padding: 10px; border-radius: 3px; overflow-x: auto; }
</style>
