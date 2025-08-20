<?php

/**
 * Script para explorar la estructura de ODS.TAB_CONFIGURACION
 * Sistema Medifarma - 2025
 */

require_once __DIR__ . '/vendor/autoload.php';
use Dotenv\Dotenv;

echo "=== EXPLORADOR TABLA ODS.TAB_CONFIGURACION ===\n\n";

// Cargar .env
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

class ConfigurationTableExplorer
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

    public function exploreConfigurationTable()
    {
        $this->checkTableExists();
        $this->showTableStructure();
        $this->showIndexes();
        $this->showConstraints();
        $this->showSampleData();
        $this->showTableStats();
    }

    private function checkTableExists()
    {
        echo "=== VERIFICANDO EXISTENCIA DE LA TABLA ===\n";
        
        try {
            $sql = "
                SELECT 
                    SCHEMA_NAME(schema_id) as schema_name,
                    name as table_name,
                    create_date,
                    modify_date
                FROM sys.tables 
                WHERE SCHEMA_NAME(schema_id) = 'ODS' 
                AND name = 'TAB_CONFIGURACION'
            ";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($result) {
                echo "✅ Tabla encontrada:\n";
                echo "   - Esquema: {$result['schema_name']}\n";
                echo "   - Nombre: {$result['table_name']}\n";
                echo "   - Creada: {$result['create_date']}\n";
                echo "   - Modificada: {$result['modify_date']}\n\n";
            } else {
                echo "❌ Tabla ODS.TAB_CONFIGURACION no encontrada\n\n";
                return false;
            }
            
        } catch (Exception $e) {
            echo "❌ Error verificando tabla: " . $e->getMessage() . "\n\n";
            return false;
        }
        
        return true;
    }

    private function showTableStructure()
    {
        echo "=== ESTRUCTURA DE LA TABLA ODS.TAB_CONFIGURACION ===\n";
        
        try {
            $sql = "
                SELECT 
                    c.COLUMN_NAME,
                    c.DATA_TYPE,
                    c.CHARACTER_MAXIMUM_LENGTH,
                    c.NUMERIC_PRECISION,
                    c.NUMERIC_SCALE,
                    c.IS_NULLABLE,
                    c.COLUMN_DEFAULT,
                    c.ORDINAL_POSITION,
                    CASE 
                        WHEN pk.COLUMN_NAME IS NOT NULL THEN 'PK'
                        WHEN fk.COLUMN_NAME IS NOT NULL THEN 'FK'
                        ELSE ''
                    END as KEY_TYPE
                FROM INFORMATION_SCHEMA.COLUMNS c
                LEFT JOIN (
                    SELECT ku.COLUMN_NAME
                    FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS tc
                    INNER JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE ku
                        ON tc.CONSTRAINT_NAME = ku.CONSTRAINT_NAME
                    WHERE tc.TABLE_SCHEMA = 'ODS' 
                    AND tc.TABLE_NAME = 'TAB_CONFIGURACION'
                    AND tc.CONSTRAINT_TYPE = 'PRIMARY KEY'
                ) pk ON c.COLUMN_NAME = pk.COLUMN_NAME
                LEFT JOIN (
                    SELECT ku.COLUMN_NAME
                    FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS tc
                    INNER JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE ku
                        ON tc.CONSTRAINT_NAME = ku.CONSTRAINT_NAME
                    WHERE tc.TABLE_SCHEMA = 'ODS' 
                    AND tc.TABLE_NAME = 'TAB_CONFIGURACION'
                    AND tc.CONSTRAINT_TYPE = 'FOREIGN KEY'
                ) fk ON c.COLUMN_NAME = fk.COLUMN_NAME
                WHERE c.TABLE_SCHEMA = 'ODS' 
                AND c.TABLE_NAME = 'TAB_CONFIGURACION'
                ORDER BY c.ORDINAL_POSITION
            ";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($columns)) {
                echo "❌ No se encontraron columnas\n\n";
                return;
            }
            
            echo sprintf("%-4s %-30s %-15s %-10s %-10s %-8s %-5s %-20s\n", 
                'Pos', 'Columna', 'Tipo', 'Longitud', 'Precisión', 'Escala', 'Nulo', 'Default');
            echo str_repeat('-', 120) . "\n";
            
            foreach ($columns as $column) {
                $length = $column['CHARACTER_MAXIMUM_LENGTH'] ?: 
                         ($column['NUMERIC_PRECISION'] ? $column['NUMERIC_PRECISION'] : '-');
                $scale = $column['NUMERIC_SCALE'] ?: '-';
                $nullable = $column['IS_NULLABLE'] === 'YES' ? 'SÍ' : 'NO';
                $default = $column['COLUMN_DEFAULT'] ?: '-';
                $keyType = $column['KEY_TYPE'] ? " ({$column['KEY_TYPE']})" : '';
                
                echo sprintf("%-4s %-30s %-15s %-10s %-10s %-8s %-5s %-20s\n",
                    $column['ORDINAL_POSITION'],
                    $column['COLUMN_NAME'] . $keyType,
                    $column['DATA_TYPE'],
                    $length,
                    $column['NUMERIC_PRECISION'] ?: '-',
                    $scale,
                    $nullable,
                    substr($default, 0, 20)
                );
            }
            
            echo "\n";
            
        } catch (Exception $e) {
            echo "❌ Error obteniendo estructura: " . $e->getMessage() . "\n\n";
        }
    }

    private function showIndexes()
    {
        echo "=== ÍNDICES DE LA TABLA ===\n";
        
        try {
            $sql = "
                SELECT 
                    i.name as index_name,
                    i.type_desc as index_type,
                    i.is_unique,
                    i.is_primary_key,
                    STRING_AGG(c.name, ', ') WITHIN GROUP (ORDER BY ic.key_ordinal) as columns
                FROM sys.indexes i
                INNER JOIN sys.index_columns ic ON i.object_id = ic.object_id AND i.index_id = ic.index_id
                INNER JOIN sys.columns c ON ic.object_id = c.object_id AND ic.column_id = c.column_id
                INNER JOIN sys.tables t ON i.object_id = t.object_id
                INNER JOIN sys.schemas s ON t.schema_id = s.schema_id
                WHERE s.name = 'ODS' AND t.name = 'TAB_CONFIGURACION'
                GROUP BY i.name, i.type_desc, i.is_unique, i.is_primary_key
                ORDER BY i.is_primary_key DESC, i.is_unique DESC, i.name
            ";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $indexes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($indexes)) {
                echo "ℹ️ No se encontraron índices\n\n";
                return;
            }
            
            echo sprintf("%-30s %-15s %-8s %-8s %-40s\n", 
                'Nombre', 'Tipo', 'Único', 'PK', 'Columnas');
            echo str_repeat('-', 100) . "\n";
            
            foreach ($indexes as $index) {
                $unique = $index['is_unique'] ? 'SÍ' : 'NO';
                $pk = $index['is_primary_key'] ? 'SÍ' : 'NO';
                
                echo sprintf("%-30s %-15s %-8s %-8s %-40s\n",
                    $index['index_name'],
                    $index['index_type'],
                    $unique,
                    $pk,
                    $index['columns']
                );
            }
            
            echo "\n";
            
        } catch (Exception $e) {
            echo "❌ Error obteniendo índices: " . $e->getMessage() . "\n\n";
        }
    }

    private function showConstraints()
    {
        echo "=== RESTRICCIONES DE LA TABLA ===\n";
        
        try {
            $sql = "
                SELECT 
                    tc.CONSTRAINT_NAME,
                    tc.CONSTRAINT_TYPE,
                    STRING_AGG(kcu.COLUMN_NAME, ', ') as columns,
                    rc.UNIQUE_CONSTRAINT_NAME as referenced_constraint
                FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS tc
                LEFT JOIN INFORMATION_SCHEMA.KEY_COLUMN_USAGE kcu 
                    ON tc.CONSTRAINT_NAME = kcu.CONSTRAINT_NAME
                LEFT JOIN INFORMATION_SCHEMA.REFERENTIAL_CONSTRAINTS rc 
                    ON tc.CONSTRAINT_NAME = rc.CONSTRAINT_NAME
                WHERE tc.TABLE_SCHEMA = 'ODS' 
                AND tc.TABLE_NAME = 'TAB_CONFIGURACION'
                GROUP BY tc.CONSTRAINT_NAME, tc.CONSTRAINT_TYPE, rc.UNIQUE_CONSTRAINT_NAME
                ORDER BY tc.CONSTRAINT_TYPE, tc.CONSTRAINT_NAME
            ";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $constraints = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($constraints)) {
                echo "ℹ️ No se encontraron restricciones\n\n";
                return;
            }
            
            echo sprintf("%-40s %-20s %-30s %-30s\n", 
                'Nombre', 'Tipo', 'Columnas', 'Referencia');
            echo str_repeat('-', 120) . "\n";
            
            foreach ($constraints as $constraint) {
                echo sprintf("%-40s %-20s %-30s %-30s\n",
                    $constraint['CONSTRAINT_NAME'],
                    $constraint['CONSTRAINT_TYPE'],
                    $constraint['columns'],
                    $constraint['referenced_constraint'] ?: '-'
                );
            }
            
            echo "\n";
            
        } catch (Exception $e) {
            echo "❌ Error obteniendo restricciones: " . $e->getMessage() . "\n\n";
        }
    }

    private function showSampleData()
    {
        echo "=== DATOS DE MUESTRA ===\n";
        
        try {
            // Primero contar registros
            $countStmt = $this->pdo->prepare("SELECT COUNT(*) as total FROM ODS.TAB_CONFIGURACION");
            $countStmt->execute();
            $total = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            echo "Total de registros: {$total}\n\n";
            
            if ($total > 0) {
                // Mostrar los primeros 5 registros
                $stmt = $this->pdo->prepare("SELECT TOP 5 * FROM ODS.TAB_CONFIGURACION");
                $stmt->execute();
                $samples = $stmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (!empty($samples)) {
                    echo "Primeros 5 registros:\n";
                    
                    // Mostrar headers
                    $headers = array_keys($samples[0]);
                    foreach ($headers as $header) {
                        echo sprintf("%-20s ", substr($header, 0, 20));
                    }
                    echo "\n" . str_repeat('-', count($headers) * 21) . "\n";
                    
                    // Mostrar datos
                    foreach ($samples as $row) {
                        foreach ($row as $value) {
                            $displayValue = $value !== null ? substr((string)$value, 0, 20) : 'NULL';
                            echo sprintf("%-20s ", $displayValue);
                        }
                        echo "\n";
                    }
                }
            } else {
                echo "La tabla está vacía.\n";
            }
            
            echo "\n";
            
        } catch (Exception $e) {
            echo "❌ Error obteniendo datos de muestra: " . $e->getMessage() . "\n\n";
        }
    }

    private function showTableStats()
    {
        echo "=== ESTADÍSTICAS DE LA TABLA ===\n";
        
        try {
            $sql = "
                SELECT 
                    s.name as schema_name,
                    t.name as table_name,
                    p.rows as row_count,
                    SUM(a.total_pages) * 8 as total_space_kb,
                    SUM(a.used_pages) * 8 as used_space_kb,
                    (SUM(a.total_pages) - SUM(a.used_pages)) * 8 as unused_space_kb
                FROM sys.tables t
                INNER JOIN sys.schemas s ON t.schema_id = s.schema_id
                INNER JOIN sys.indexes i ON t.object_id = i.object_id
                INNER JOIN sys.partitions p ON i.object_id = p.object_id AND i.index_id = p.index_id
                INNER JOIN sys.allocation_units a ON p.partition_id = a.container_id
                WHERE s.name = 'ODS' AND t.name = 'TAB_CONFIGURACION'
                GROUP BY s.name, t.name, p.rows
            ";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($stats) {
                echo "Esquema: {$stats['schema_name']}\n";
                echo "Tabla: {$stats['table_name']}\n";
                echo "Filas: " . number_format($stats['row_count']) . "\n";
                echo "Espacio total: " . number_format($stats['total_space_kb']) . " KB\n";
                echo "Espacio usado: " . number_format($stats['used_space_kb']) . " KB\n";
                echo "Espacio libre: " . number_format($stats['unused_space_kb']) . " KB\n";
            }
            
            echo "\n";
            
        } catch (Exception $e) {
            echo "❌ Error obteniendo estadísticas: " . $e->getMessage() . "\n\n";
        }
    }
}

// Ejecutar exploración
try {
    $explorer = new ConfigurationTableExplorer();
    $explorer->exploreConfigurationTable();
    echo "=== EXPLORACIÓN COMPLETADA ===\n";
} catch (Exception $e) {
    echo "❌ Error general: " . $e->getMessage() . "\n";
}

?>
