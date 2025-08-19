<?php

/**
 * Comandos para explorar las tablas ODS.TAB_MERCADO y DM.MERCADO
 * Sistema Medifarma - 2025
 */

require_once __DIR__ . '/vendor/autoload.php';
use Dotenv\Dotenv;

// Cargar .env
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "=== EXPLORADOR INTERACTIVO DE BD ===\n\n";

try {
    $dsn = "sqlsrv:Server={$_ENV['DB_HOST']},{$_ENV['DB_PORT']};Database={$_ENV['DB_DATABASE']}";
    $pdo = new PDO($dsn, $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "✅ Conexión establecida\n\n";
} catch (Exception $e) {
    die("❌ Error: " . $e->getMessage() . "\n");
}

// Función para ejecutar consultas y mostrar resultados
function ejecutarConsulta($pdo, $sql, $titulo = '') {
    echo "=== {$titulo} ===\n";
    echo "SQL: {$sql}\n\n";
    
    try {
        $stmt = $pdo->query($sql);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (empty($results)) {
            echo "No hay resultados.\n\n";
            return;
        }
        
        // Mostrar resultados en formato tabla
        $columns = array_keys($results[0]);
        
        // Encabezados
        foreach ($columns as $col) {
            echo str_pad($col, 25);
        }
        echo "\n" . str_repeat("-", count($columns) * 25) . "\n";
        
        // Datos (máximo 10 filas)
        $count = 0;
        foreach ($results as $row) {
            if ($count >= 10) {
                echo "... (mostrando solo primeros 10 registros)\n";
                break;
            }
            
            foreach ($row as $value) {
                $displayValue = is_null($value) ? 'NULL' : substr(strval($value), 0, 23);
                echo str_pad($displayValue, 25);
            }
            echo "\n";
            $count++;
        }
        
        echo "\nTotal registros: " . count($results) . "\n\n";
        
    } catch (Exception $e) {
        echo "❌ Error: " . $e->getMessage() . "\n\n";
    }
}

echo "🔍 EXPLORANDO ESTRUCTURA Y DATOS...\n\n";

// 1. Ver estructura de ODS.TAB_MERCADO
ejecutarConsulta($pdo, "
    SELECT 
        COLUMN_NAME as Columna,
        DATA_TYPE as Tipo,
        CHARACTER_MAXIMUM_LENGTH as Longitud,
        IS_NULLABLE as Permite_Nulo,
        COLUMN_DEFAULT as Valor_Default
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'ODS' AND TABLE_NAME = 'TAB_MERCADO'
    ORDER BY ORDINAL_POSITION
", "ESTRUCTURA ODS.TAB_MERCADO");

// 2. Ver estructura de DM.MERCADO
ejecutarConsulta($pdo, "
    SELECT 
        COLUMN_NAME as Columna,
        DATA_TYPE as Tipo,
        CHARACTER_MAXIMUM_LENGTH as Longitud,
        IS_NULLABLE as Permite_Nulo
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'DM' AND TABLE_NAME = 'MERCADO'
    ORDER BY ORDINAL_POSITION
", "ESTRUCTURA DM.MERCADO");

// 3. Contar registros en ambas
ejecutarConsulta($pdo, "SELECT COUNT(*) as Total_Registros FROM ODS.TAB_MERCADO", "TOTAL REGISTROS ODS.TAB_MERCADO");

ejecutarConsulta($pdo, "SELECT COUNT(*) as Total_Registros FROM DM.MERCADO", "TOTAL REGISTROS DM.MERCADO");

// 4. Ver datos de muestra de ODS.TAB_MERCADO
ejecutarConsulta($pdo, "SELECT TOP 5 * FROM ODS.TAB_MERCADO", "MUESTRA ODS.TAB_MERCADO");

// 5. Ver datos de muestra de DM.MERCADO
ejecutarConsulta($pdo, "SELECT TOP 5 * FROM DM.MERCADO", "MUESTRA DM.MERCADO");

// 6. Ver si hay llaves primarias
ejecutarConsulta($pdo, "
    SELECT 
        COLUMN_NAME as Columna_Clave_Primaria
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = 'ODS' 
    AND TABLE_NAME = 'TAB_MERCADO'
    AND CONSTRAINT_NAME LIKE 'PK_%'
", "CLAVES PRIMARIAS ODS.TAB_MERCADO");

ejecutarConsulta($pdo, "
    SELECT 
        COLUMN_NAME as Columna_Clave_Primaria
    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
    WHERE TABLE_SCHEMA = 'DM' 
    AND TABLE_NAME = 'MERCADO'
    AND CONSTRAINT_NAME LIKE 'PK_%'
", "CLAVES PRIMARIAS DM.MERCADO");

// 7. Buscar posibles campos ID
ejecutarConsulta($pdo, "
    SELECT 
        COLUMN_NAME as Posible_ID
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'ODS' AND TABLE_NAME = 'TAB_MERCADO'
    AND (COLUMN_NAME LIKE '%ID%' OR COLUMN_NAME LIKE '%_ID' OR COLUMN_NAME LIKE 'ID_%')
", "POSIBLES CAMPOS ID EN ODS.TAB_MERCADO");

ejecutarConsulta($pdo, "
    SELECT 
        COLUMN_NAME as Posible_ID
    FROM INFORMATION_SCHEMA.COLUMNS 
    WHERE TABLE_SCHEMA = 'DM' AND TABLE_NAME = 'MERCADO'
    AND (COLUMN_NAME LIKE '%ID%' OR COLUMN_NAME LIKE '%_ID' OR COLUMN_NAME LIKE 'ID_%')
", "POSIBLES CAMPOS ID EN DM.MERCADO");

echo "=== EXPLORACIÓN COMPLETADA ===\n";
echo "Ahora podemos crear el modelo y controlador adaptado a tu estructura.\n";
