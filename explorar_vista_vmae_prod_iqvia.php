<?php

/**
 * Script para explorar la vista dbo.VMAE_PROD_IQVIA
 * Sistema Medifarma - 2025
 */

require_once __DIR__ . '/vendor/autoload.php';
use Dotenv\Dotenv;

echo "=== EXPLORADOR VISTA dbo.VMAE_PROD_IQVIA ===\n\n";

// Cargar .env
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

class ViewExplorer
{
    private $pdo;

    public function __construct()
    {
        try {
            $dsn = "sqlsrv:Server={$_ENV['DB_HOST']},{$_ENV['DB_PORT']};Database={$_ENV['DB_DATABASE']};TrustServerCertificate=true";
            $this->pdo = new PDO($dsn, $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::SQLSRV_ATTR_ENCODING => PDO::SQLSRV_ENCODING_UTF8
            ]);
            
            echo "✅ Conexión establecida exitosamente\n\n";
        } catch (Exception $e) {
            die("❌ Error de conexión: " . $e->getMessage() . "\n");
        }
    }

    public function exploreView()
    {
        $this->checkViewExists();
        $this->showViewInfo();
        $this->showViewStructure();
        $this->showSampleData();
        $this->showViewStats();
    }

    private function checkViewExists()
    {
        echo "=== VERIFICANDO EXISTENCIA DE LA VISTA ===\n";
        
        try {
            $sql = "
                SELECT 
                    SCHEMA_NAME(schema_id) as schema_name,
                    name as view_name,
                    type_desc,
                    create_date,
                    modify_date
                FROM sys.views 
                WHERE SCHEMA_NAME(schema_id) = 'dbo' 
                AND name = 'VMAE_PROD_IQVIA'
            ";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                echo "✅ Vista encontrada:\n";
                echo "   - Esquema: {$result['schema_name']}\n";
                echo "   - Nombre: {$result['view_name']}\n";
                echo "   - Tipo: {$result['type_desc']}\n";
                echo "   - Creada: {$result['create_date']}\n";
                echo "   - Modificada: {$result['modify_date']}\n\n";
                return true;
            } else {
                echo "❌ Vista dbo.VMAE_PROD_IQVIA no encontrada\n\n";
                return false;
            }
            
        } catch (Exception $e) {
            echo "❌ Error verificando vista: " . $e->getMessage() . "\n\n";
            return false;
        }
    }

    private function showViewInfo()
    {
        echo "=== INFORMACIÓN DE LA VISTA ===\n";
        
        try {
            $sql = "
                SELECT 
                    v.name as view_name,
                    m.definition as view_definition
                FROM sys.views v
                INNER JOIN sys.sql_modules m ON v.object_id = m.object_id
                WHERE SCHEMA_NAME(v.schema_id) = 'dbo' 
                AND v.name = 'VMAE_PROD_IQVIA'
            ";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                echo "Nombre: {$result['view_name']}\n\n";
                echo "Definición de la vista:\n";
                echo "-----------------------------------\n";
                
                // Mostrar solo las primeras líneas de la definición para no saturar
                $lines = explode("\n", $result['view_definition']);
                $previewLines = array_slice($lines, 0, 30);
                echo implode("\n", $previewLines);
                
                if (count($lines) > 30) {
                    echo "\n... (definición truncada, " . (count($lines) - 30) . " líneas más)\n";
                }
                echo "\n\n";
            }
            
        } catch (Exception $e) {
            echo "❌ Error obteniendo información de la vista: " . $e->getMessage() . "\n\n";
        }
    }

    private function showViewStructure()
    {
        echo "=== ESTRUCTURA DE LA VISTA ===\n";
        
        try {
            $sql = "
                SELECT 
                    c.COLUMN_NAME,
                    c.DATA_TYPE,
                    c.CHARACTER_MAXIMUM_LENGTH,
                    c.NUMERIC_PRECISION,
                    c.NUMERIC_SCALE,
                    c.IS_NULLABLE,
                    c.ORDINAL_POSITION
                FROM INFORMATION_SCHEMA.COLUMNS c
                WHERE c.TABLE_SCHEMA = 'dbo' 
                AND c.TABLE_NAME = 'VMAE_PROD_IQVIA'
                ORDER BY c.ORDINAL_POSITION
            ";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($columns)) {
                echo "❌ No se encontraron columnas\n\n";
                return;
            }
            
            echo "Total de columnas: " . count($columns) . "\n\n";
            echo sprintf("%-4s %-30s %-15s %-10s %-10s %-8s %-5s\n", 
                'Pos', 'Columna', 'Tipo', 'Longitud', 'Precisión', 'Escala', 'Nulo');
            echo str_repeat('-', 90) . "\n";
            
            foreach ($columns as $column) {
                $length = $column['CHARACTER_MAXIMUM_LENGTH'] ?: 
                         ($column['NUMERIC_PRECISION'] ? $column['NUMERIC_PRECISION'] : '-');
                $scale = $column['NUMERIC_SCALE'] ?: '-';
                $nullable = $column['IS_NULLABLE'] === 'YES' ? 'SÍ' : 'NO';
                
                echo sprintf("%-4s %-30s %-15s %-10s %-10s %-8s %-5s\n",
                    $column['ORDINAL_POSITION'],
                    substr($column['COLUMN_NAME'], 0, 30),
                    $column['DATA_TYPE'],
                    $length,
                    $column['NUMERIC_PRECISION'] ?: '-',
                    $scale,
                    $nullable
                );
            }
            
            echo "\n";
            
        } catch (Exception $e) {
            echo "❌ Error obteniendo estructura: " . $e->getMessage() . "\n\n";
        }
    }

    private function showSampleData()
    {
        echo "=== DATOS DE MUESTRA (PRIMEROS 10 REGISTROS) ===\n";
        
        try {
            // Primero contar registros total
            $countStmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM dbo.VMAE_PROD_IQVIA");
            $countStmt->execute();
            $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            echo "Total de registros en la vista: " . number_format($total) . "\n\n";
            
            if ($total > 0) {
                // Mostrar los primeros 10 registros
                $stmt = $this->pdo->prepare("SELECT TOP 10 * FROM dbo.VMAE_PROD_IQVIA");
                $stmt->execute();
                $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (!empty($samples)) {
                    echo "Primeros 10 registros:\n";
                    
                    // Mostrar headers (solo primeras columnas para que sea legible)
                    $headers = array_keys($samples[0]);
                    $maxCols = min(8, count($headers)); // Mostrar máximo 8 columnas
                    
                    for ($i = 0; $i < $maxCols; $i++) {
                        echo sprintf("%-20s ", substr($headers[$i], 0, 20));
                    }
                    if (count($headers) > $maxCols) {
                        echo "... (+" . (count($headers) - $maxCols) . " cols más)";
                    }
                    echo "\n" . str_repeat('-', $maxCols * 21) . "\n";
                    
                    // Mostrar datos
                    foreach ($samples as $row) {
                        $values = array_values($row);
                        for ($i = 0; $i < $maxCols; $i++) {
                            $displayValue = $values[$i] !== null ? substr((string)$values[$i], 0, 20) : 'NULL';
                            echo sprintf("%-20s ", $displayValue);
                        }
                        if (count($values) > $maxCols) {
                            echo "...";
                        }
                        echo "\n";
                    }
                    
                    echo "\nℹ️ Mostrando solo las primeras " . $maxCols . " columnas de " . count($headers) . " total\n";
                }
            } else {
                echo "La vista está vacía.\n";
            }
            
            echo "\n";
            
        } catch (Exception $e) {
            echo "❌ Error obteniendo datos de muestra: " . $e->getMessage() . "\n\n";
        }
    }

    private function showViewStats()
    {
        echo "=== ESTADÍSTICAS DE LA VISTA ===\n";
        
        try {
            // Obtener estadísticas básicas
            $sql = "
                SELECT 
                    v.name as view_name,
                    SCHEMA_NAME(v.schema_id) as schema_name,
                    v.create_date,
                    v.modify_date
                FROM sys.views v
                WHERE SCHEMA_NAME(v.schema_id) = 'dbo' 
                AND v.name = 'VMAE_PROD_IQVIA'
            ";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($stats) {
                echo "Esquema: {$stats['schema_name']}\n";
                echo "Vista: {$stats['view_name']}\n";
                echo "Fecha de creación: {$stats['create_date']}\n";
                echo "Última modificación: {$stats['modify_date']}\n";
            }
            
            // Intentar obtener información de las tablas base
            try {
                $dependenciesSql = "
                    SELECT DISTINCT
                        d.referenced_schema_name as schema_name,
                        d.referenced_entity_name as table_name
                    FROM sys.dm_sql_referenced_entities('dbo.VMAE_PROD_IQVIA', 'OBJECT') d
                    WHERE d.referenced_schema_name IS NOT NULL
                    AND d.referenced_entity_name IS NOT NULL
                ";
                
                $depsStmt = $this->pdo->prepare($dependenciesSql);
                $depsStmt->execute();
                $dependencies = $depsStmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (!empty($dependencies)) {
                    echo "\nTablas base utilizadas:\n";
                    foreach ($dependencies as $dep) {
                        echo "   - {$dep['schema_name']}.{$dep['table_name']}\n";
                    }
                }
                
            } catch (Exception $e) {
                echo "\nNo se pudieron obtener las dependencias.\n";
            }
            
            echo "\n";
            
        } catch (Exception $e) {
            echo "❌ Error obteniendo estadísticas: " . $e->getMessage() . "\n\n";
        }
    }
}

// Ejecutar exploración
try {
    $explorer = new ViewExplorer();
    $explorer->exploreView();
    echo "=== EXPLORACIÓN COMPLETADA ===\n";
} catch (Exception $e) {
    echo "❌ Error general: " . $e->getMessage() . "\n";
}

?>
