<?php

/**
 * Script rápido para explorar la vista dbo.VMAE_PROD_IQVIA
 * Sistema Medifarma - 2025
 */

require_once __DIR__ . '/vendor/autoload.php';
use Dotenv\Dotenv;

echo "=== EXPLORADOR RÁPIDO VISTA dbo.VMAE_PROD_IQVIA ===\n\n";

// Cargar .env
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

try {
    $dsn = "sqlsrv:Server={$_ENV['DB_HOST']},{$_ENV['DB_PORT']};Database={$_ENV['DB_DATABASE']};TrustServerCertificate=true";
    $pdo = new PDO($dsn, $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::SQLSRV_ATTR_ENCODING => PDO::SQLSRV_ENCODING_UTF8
    ]);
    
    echo "✅ Conexión establecida exitosamente\n\n";
    
    // 1. Información básica
    echo "=== INFORMACIÓN BÁSICA ===\n";
    $stmt = $pdo->query("
        SELECT 
            SCHEMA_NAME(schema_id) as schema_name,
            name as view_name,
            create_date,
            modify_date
        FROM sys.views 
        WHERE SCHEMA_NAME(schema_id) = 'dbo' 
        AND name = 'VMAE_PROD_IQVIA'
    ");
    $info = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Vista: {$info['schema_name']}.{$info['view_name']}\n";
    echo "Creada: {$info['create_date']}\n";
    echo "Modificada: {$info['modify_date']}\n\n";
    
    // 2. Estructura completa de columnas
    echo "=== ESTRUCTURA COMPLETA DE COLUMNAS ===\n";
    $stmt = $pdo->query("
        SELECT 
            ORDINAL_POSITION,
            COLUMN_NAME,
            DATA_TYPE,
            CHARACTER_MAXIMUM_LENGTH,
            NUMERIC_PRECISION,
            IS_NULLABLE
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = 'dbo' 
        AND TABLE_NAME = 'VMAE_PROD_IQVIA'
        ORDER BY ORDINAL_POSITION
    ");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "Total de columnas: " . count($columns) . "\n\n";
    echo sprintf("%-4s %-35s %-15s %-10s %-5s\n", 'Pos', 'Columna', 'Tipo', 'Longitud', 'Nulo');
    echo str_repeat('-', 75) . "\n";
    
    foreach ($columns as $col) {
        $length = $col['CHARACTER_MAXIMUM_LENGTH'] ?: ($col['NUMERIC_PRECISION'] ?: '-');
        $nullable = $col['IS_NULLABLE'] === 'YES' ? 'SÍ' : 'NO';
        echo sprintf("%-4s %-35s %-15s %-10s %-5s\n", 
            $col['ORDINAL_POSITION'],
            substr($col['COLUMN_NAME'], 0, 35), 
            $col['DATA_TYPE'], 
            $length, 
            $nullable
        );
    }
    echo "\n";
    
    // 3. Muestra de solo 10 registros con las primeras 8 columnas
    echo "=== MUESTRA DE DATOS (10 registros, primeras 8 columnas) ===\n";
    echo "ℹ️ Nota: La vista tiene más de 15k registros, mostrando solo muestra...\n\n";
    
    $stmt = $pdo->query("
        SELECT TOP 10 
            [Código_Presentación],
            [Descripción_Presentación],
            [Código_Producto],
            [Marca_Genérico],
            [Molécula],
            [MERCADO],
            [Laboratorio],
            [Fuente]
        FROM dbo.VMAE_PROD_IQVIA
    ");
    $sample = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($sample)) {
        $headers = array_keys($sample[0]);
        foreach ($headers as $header) {
            echo sprintf("%-20s ", substr($header, 0, 20));
        }
        echo "\n" . str_repeat('-', count($headers) * 21) . "\n";
        
        foreach ($sample as $row) {
            foreach ($row as $value) {
                $displayValue = $value !== null ? substr((string)$value, 0, 20) : 'NULL';
                echo sprintf("%-20s ", $displayValue);
            }
            echo "\n";
        }
    }
    echo "\n";
    
    // 4. Valores de muestra en columnas clave
    echo "=== VALORES DE MUESTRA EN COLUMNAS CLAVE ===\n";
    
    $sampleQueries = [
        'MERCADO' => "SELECT DISTINCT TOP 5 [MERCADO] FROM dbo.VMAE_PROD_IQVIA WHERE [MERCADO] IS NOT NULL",
        'Laboratorio' => "SELECT DISTINCT TOP 5 [Laboratorio] FROM dbo.VMAE_PROD_IQVIA WHERE [Laboratorio] IS NOT NULL",
        'Marca_Genérico' => "SELECT DISTINCT TOP 5 [Marca_Genérico] FROM dbo.VMAE_PROD_IQVIA WHERE [Marca_Genérico] IS NOT NULL",
        'Fuente' => "SELECT DISTINCT [Fuente] FROM dbo.VMAE_PROD_IQVIA WHERE [Fuente] IS NOT NULL"
    ];
    
    foreach ($sampleQueries as $column => $query) {
        try {
            echo "- $column:\n";
            $stmt = $pdo->query($query);
            $results = $stmt->fetchAll(PDO::FETCH_COLUMN);
            foreach ($results as $value) {
                echo "  • " . substr($value, 0, 50) . "\n";
            }
            echo "\n";
        } catch (Exception $e) {
            echo "  Error obteniendo valores de muestra\n\n";
        }
    }
    
    // 5. Información de la definición de la vista
    echo "=== DEFINICIÓN DE LA VISTA (PRIMERAS LÍNEAS) ===\n";
    try {
        $stmt = $pdo->query("
            SELECT TOP 1 m.definition 
            FROM sys.views v
            INNER JOIN sys.sql_modules m ON v.object_id = m.object_id
            WHERE SCHEMA_NAME(v.schema_id) = 'dbo' 
            AND v.name = 'VMAE_PROD_IQVIA'
        ");
        $definition = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($definition) {
            $lines = explode("\n", $definition['definition']);
            $previewLines = array_slice($lines, 0, 15);
            echo implode("\n", $previewLines);
            echo "\n... (definición truncada)\n\n";
        }
    } catch (Exception $e) {
        echo "No se pudo obtener la definición de la vista.\n\n";
    }
    
    echo "=== EXPLORACIÓN COMPLETADA ===\n";
    echo "ℹ️ Vista con " . count($columns) . " columnas y más de 15k registros\n";
    echo "ℹ️ Principales campos: Código_Presentación, Molécula, MERCADO, Laboratorio\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

?>
