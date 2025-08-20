<?php

/**
 * Script simplificado para explorar la vista dbo.VMAE_PROD_IQVIA
 * Sistema Medifarma - 2025
 */

require_once __DIR__ . '/vendor/autoload.php';
use Dotenv\Dotenv;

echo "=== EXPLORADOR VISTA dbo.VMAE_PROD_IQVIA (SIMPLIFICADO) ===\n\n";

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
    
    // 2. Contar total de registros
    echo "=== CONTEO TOTAL ===\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM dbo.VMAE_PROD_IQVIA");
    $count = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total de registros: " . number_format($count['total']) . "\n\n";
    
    // 3. Estructura de columnas (solo nombres y tipos)
    echo "=== COLUMNAS PRINCIPALES ===\n";
    $stmt = $pdo->query("
        SELECT TOP 20
            COLUMN_NAME,
            DATA_TYPE,
            CHARACTER_MAXIMUM_LENGTH,
            IS_NULLABLE
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = 'dbo' 
        AND TABLE_NAME = 'VMAE_PROD_IQVIA'
        ORDER BY ORDINAL_POSITION
    ");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo sprintf("%-30s %-15s %-10s %-5s\n", 'Columna', 'Tipo', 'Longitud', 'Nulo');
    echo str_repeat('-', 65) . "\n";
    foreach ($columns as $col) {
        $length = $col['CHARACTER_MAXIMUM_LENGTH'] ?: '-';
        $nullable = $col['IS_NULLABLE'] === 'YES' ? 'SÍ' : 'NO';
        echo sprintf("%-30s %-15s %-10s %-5s\n", 
            substr($col['COLUMN_NAME'], 0, 30), 
            $col['DATA_TYPE'], 
            $length, 
            $nullable
        );
    }
    echo "\n";
    
    // 4. Muestra de datos (solo primeras 5 columnas y 5 filas)
    echo "=== MUESTRA DE DATOS (5 filas x 5 columnas) ===\n";
    $stmt = $pdo->query("
        SELECT TOP 5 
            [Código_Presentación],
            [Descripción_Presentación],
            [Código_Producto],
            [Molécula],
            [MERCADO]
        FROM dbo.VMAE_PROD_IQVIA
    ");
    $sample = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($sample)) {
        $headers = array_keys($sample[0]);
        foreach ($headers as $header) {
            echo sprintf("%-25s ", substr($header, 0, 25));
        }
        echo "\n" . str_repeat('-', 130) . "\n";
        
        foreach ($sample as $row) {
            foreach ($row as $value) {
                $displayValue = $value !== null ? substr((string)$value, 0, 25) : 'NULL';
                echo sprintf("%-25s ", $displayValue);
            }
            echo "\n";
        }
    }
    echo "\n";
    
    // 5. Valores únicos en columnas clave
    echo "=== VALORES ÚNICOS EN COLUMNAS CLAVE ===\n";
    
    $keyColumns = ['MERCADO', 'Laboratorio', 'Marca_Genérico'];
    foreach ($keyColumns as $column) {
        try {
            $stmt = $pdo->query("
                SELECT COUNT(DISTINCT [$column]) as unique_count 
                FROM dbo.VMAE_PROD_IQVIA 
                WHERE [$column] IS NOT NULL
            ");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            echo "- $column: " . number_format($result['unique_count']) . " valores únicos\n";
        } catch (Exception $e) {
            echo "- $column: Error obteniendo valores únicos\n";
        }
    }
    echo "\n";
    
    echo "=== EXPLORACIÓN COMPLETADA ===\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}

?>
