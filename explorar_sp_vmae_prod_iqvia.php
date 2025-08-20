<?php

/**
 * Script para explorar el stored procedure dbo.VMAE_PROD_IQVIA
 * Sistema Medifarma - 2025
 */

require_once __DIR__ . '/vendor/autoload.php';
use Dotenv\Dotenv;

echo "=== EXPLORADOR STORED PROCEDURE dbo.VMAE_PROD_IQVIA ===\n\n";

// Cargar .env
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

class StoredProcedureExplorer
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

    public function exploreStoredProcedure()
    {
        $this->checkProcedureExists();
        $this->showProcedureInfo();
        $this->showParameters();
        $this->executeProcedureAndAnalyze();
    }

    private function checkProcedureExists()
    {
        echo "=== VERIFICANDO EXISTENCIA DEL STORED PROCEDURE ===\n";
        
        try {
            $sql = "
                SELECT 
                    SCHEMA_NAME(schema_id) as schema_name,
                    name as procedure_name,
                    type_desc,
                    create_date,
                    modify_date
                FROM sys.procedures 
                WHERE SCHEMA_NAME(schema_id) = 'dbo' 
                AND name = 'VMAE_PROD_IQVIA'
            ";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                echo "✅ Stored Procedure encontrado:\n";
                echo "   - Esquema: {$result['schema_name']}\n";
                echo "   - Nombre: {$result['procedure_name']}\n";
                echo "   - Tipo: {$result['type_desc']}\n";
                echo "   - Creado: {$result['create_date']}\n";
                echo "   - Modificado: {$result['modify_date']}\n\n";
                return true;
            } else {
                echo "❌ Stored Procedure dbo.VMAE_PROD_IQVIA no encontrado\n\n";
                return false;
            }
            
        } catch (Exception $e) {
            echo "❌ Error verificando procedure: " . $e->getMessage() . "\n\n";
            return false;
        }
    }

    private function showProcedureInfo()
    {
        echo "=== INFORMACIÓN DEL STORED PROCEDURE ===\n";
        
        try {
            $sql = "
                SELECT 
                    p.name as procedure_name,
                    m.definition as procedure_definition
                FROM sys.procedures p
                INNER JOIN sys.sql_modules m ON p.object_id = m.object_id
                WHERE SCHEMA_NAME(p.schema_id) = 'dbo' 
                AND p.name = 'VMAE_PROD_IQVIA'
            ";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                echo "Nombre: {$result['procedure_name']}\n\n";
                echo "Definición del procedimiento:\n";
                echo "-----------------------------------\n";
                
                // Mostrar solo las primeras líneas de la definición para no saturar
                $lines = explode("\n", $result['procedure_definition']);
                $previewLines = array_slice($lines, 0, 20);
                echo implode("\n", $previewLines);
                
                if (count($lines) > 20) {
                    echo "\n... (definición truncada, " . (count($lines) - 20) . " líneas más)\n";
                }
                echo "\n\n";
            }
            
        } catch (Exception $e) {
            echo "❌ Error obteniendo información del procedure: " . $e->getMessage() . "\n\n";
        }
    }

    private function showParameters()
    {
        echo "=== PARÁMETROS DEL STORED PROCEDURE ===\n";
        
        try {
            $sql = "
                SELECT 
                    p.name as parameter_name,
                    TYPE_NAME(p.user_type_id) as data_type,
                    p.max_length,
                    p.precision,
                    p.scale,
                    p.is_output,
                    p.has_default_value,
                    p.default_value
                FROM sys.parameters p
                INNER JOIN sys.procedures pr ON p.object_id = pr.object_id
                WHERE SCHEMA_NAME(pr.schema_id) = 'dbo' 
                AND pr.name = 'VMAE_PROD_IQVIA'
                AND p.parameter_id > 0
                ORDER BY p.parameter_id
            ";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $parameters = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($parameters)) {
                echo "ℹ️ El stored procedure no tiene parámetros\n\n";
            } else {
                echo sprintf("%-20s %-15s %-10s %-10s %-5s %-7s %-15s\n", 
                    'Parámetro', 'Tipo', 'Longitud', 'Precisión', 'Escala', 'Output', 'Default');
                echo str_repeat('-', 100) . "\n";
                
                foreach ($parameters as $param) {
                    $output = $param['is_output'] ? 'SÍ' : 'NO';
                    $hasDefault = $param['has_default_value'] ? 'SÍ' : 'NO';
                    $default = $param['default_value'] ?: '-';
                    
                    echo sprintf("%-20s %-15s %-10s %-10s %-5s %-7s %-15s\n",
                        $param['parameter_name'],
                        $param['data_type'],
                        $param['max_length'],
                        $param['precision'] ?: '-',
                        $param['scale'] ?: '-',
                        $output,
                        substr($default, 0, 15)
                    );
                }
                echo "\n";
            }
            
        } catch (Exception $e) {
            echo "❌ Error obteniendo parámetros: " . $e->getMessage() . "\n\n";
        }
    }

    private function executeProcedureAndAnalyze()
    {
        echo "=== EJECUTANDO STORED PROCEDURE Y ANALIZANDO RESULTADOS ===\n";
        
        try {
            // Primero verificamos si necesita parámetros
            echo "ℹ️ Ejecutando dbo.VMAE_PROD_IQVIA...\n\n";
            
            // Crear una tabla temporal para capturar los resultados
            $tempTable = "#temp_vmae_results_" . uniqid();
            
            // Ejecutar el SP y capturar en tabla temporal
            $sql = "
                -- Crear tabla temporal con los resultados del SP
                SELECT TOP 10 * 
                INTO {$tempTable}
                FROM OPENROWSET('SQLNCLI', 'Server=.;Trusted_Connection=yes;', 
                    'EXEC dbo.VMAE_PROD_IQVIA')
            ";
            
            // Método alternativo: ejecutar directamente
            try {
                $stmt = $this->pdo->prepare("EXEC dbo.VMAE_PROD_IQVIA");
                $stmt->execute();
                
                // Obtener información de las columnas
                $columnCount = $stmt->columnCount();
                echo "Número de columnas retornadas: {$columnCount}\n\n";
                
                if ($columnCount > 0) {
                    echo "=== ESTRUCTURA DE LAS COLUMNAS ===\n";
                    echo sprintf("%-4s %-30s %-15s\n", 'Pos', 'Nombre', 'Tipo');
                    echo str_repeat('-', 60) . "\n";
                    
                    for ($i = 0; $i < $columnCount; $i++) {
                        $meta = $stmt->getColumnMeta($i);
                        echo sprintf("%-4s %-30s %-15s\n", 
                            $i + 1, 
                            $meta['name'], 
                            $meta['sqlsrv:decl_type'] ?? $meta['native_type'] ?? 'unknown'
                        );
                    }
                    echo "\n";
                }
                
                // Obtener solo los primeros 10 registros
                echo "=== PRIMEROS 10 REGISTROS ===\n";
                $count = 0;
                $headers = null;
                
                while (($row = $stmt->fetch(PDO::FETCH_ASSOC)) && $count < 10) {
                    if ($headers === null) {
                        $headers = array_keys($row);
                        
                        // Mostrar headers
                        foreach ($headers as $header) {
                            echo sprintf("%-20s ", substr($header, 0, 20));
                        }
                        echo "\n" . str_repeat('-', count($headers) * 21) . "\n";
                    }
                    
                    // Mostrar datos
                    foreach ($row as $value) {
                        $displayValue = $value !== null ? substr((string)$value, 0, 20) : 'NULL';
                        echo sprintf("%-20s ", $displayValue);
                    }
                    echo "\n";
                    $count++;
                }
                
                if ($count === 0) {
                    echo "El stored procedure no retornó resultados.\n";
                } else {
                    echo "\nMostrados {$count} registros de muestra.\n";
                }
                
                // Contar total de registros (si es posible)
                try {
                    $stmt->nextRowset(); // Saltar al siguiente conjunto de resultados si existe
                    $countStmt = $this->pdo->prepare("
                        DECLARE @count INT;
                        INSERT INTO #temp_count_results
                        EXEC dbo.VMAE_PROD_IQVIA;
                        SELECT @count = COUNT(*) FROM #temp_count_results;
                        SELECT @count as total_records;
                    ");
                    // Esto es complejo de hacer sin conocer la estructura exacta
                    echo "\nℹ️ Para obtener el conteo total, ejecute: EXEC dbo.VMAE_PROD_IQVIA y cuente los resultados\n";
                } catch (Exception $e) {
                    echo "\nℹ️ No se pudo obtener el conteo total automáticamente\n";
                }
                
            } catch (Exception $e) {
                echo "❌ Error ejecutando el stored procedure: " . $e->getMessage() . "\n";
                
                // Intentar método alternativo para obtener solo información
                echo "\n=== INFORMACIÓN ALTERNATIVA ===\n";
                $this->getAlternativeInfo();
            }
            
        } catch (Exception $e) {
            echo "❌ Error general: " . $e->getMessage() . "\n\n";
        }
    }

    private function getAlternativeInfo()
    {
        try {
            echo "Obteniendo información del sistema sobre el stored procedure...\n\n";
            
            // Obtener dependencias
            $sql = "
                SELECT 
                    d.referenced_schema_name,
                    d.referenced_entity_name,
                    d.referenced_column_name
                FROM sys.dm_sql_referenced_entities('dbo.VMAE_PROD_IQVIA', 'OBJECT') d
                WHERE d.referenced_schema_name IS NOT NULL
            ";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $dependencies = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($dependencies)) {
                echo "=== DEPENDENCIAS DEL STORED PROCEDURE ===\n";
                echo sprintf("%-20s %-30s %-30s\n", 'Esquema', 'Objeto', 'Columna');
                echo str_repeat('-', 80) . "\n";
                
                foreach ($dependencies as $dep) {
                    echo sprintf("%-20s %-30s %-30s\n",
                        $dep['referenced_schema_name'] ?: '-',
                        $dep['referenced_entity_name'] ?: '-',
                        $dep['referenced_column_name'] ?: '-'
                    );
                }
                echo "\n";
            }
            
        } catch (Exception $e) {
            echo "No se pudo obtener información adicional: " . $e->getMessage() . "\n";
        }
    }
}

// Ejecutar exploración
try {
    $explorer = new StoredProcedureExplorer();
    $explorer->exploreStoredProcedure();
    echo "=== EXPLORACIÓN COMPLETADA ===\n";
} catch (Exception $e) {
    echo "❌ Error general: " . $e->getMessage() . "\n";
}

?>
