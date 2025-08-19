<?php

/**
 * Script para explorar la estructura de la nueva base de datos
 * Sistema Medifarma - 2025
 */

require_once __DIR__ . '/vendor/autoload.php';
use Dotenv\Dotenv;

echo "=== EXPLORADOR DE ESTRUCTURA BD ===\n\n";

// Cargar .env
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

class DatabaseExplorer
{
    private $pdo;

    public function __construct()
    {
        try {
            $dsn = "sqlsrv:Server={$_ENV['DB_HOST']},{$_ENV['DB_PORT']};Database={$_ENV['DB_DATABASE']};TrustServerCertificate=true";
            $this->pdo = new PDO($dsn, $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], [
                PDO::ATTR_TIMEOUT => 30,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ]);
            
            echo "✅ Conexión establecida exitosamente\n\n";
        } catch (Exception $e) {
            die("❌ Error de conexión: " . $e->getMessage() . "\n");
        }
    }

    public function exploreDatabase()
    {
        $this->showDatabaseInfo();
        $this->exploreTableStructure('ODS.TAB_MERCADO');
        $this->exploreViewStructure('DM.MERCADO');
        $this->showSampleData();
    }

    private function showDatabaseInfo()
    {
        echo "=== INFORMACIÓN DE LA BASE DE DATOS ===\n";
        
        try {
            $stmt = $this->pdo->query("SELECT DB_NAME() as database_name, @@VERSION as version");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo "Base de datos: " . $result['database_name'] . "\n";
            echo "Versión SQL Server: " . substr($result['version'], 0, 100) . "...\n\n";
        } catch (Exception $e) {
            echo "Error obteniendo info de BD: " . $e->getMessage() . "\n\n";
        }
    }

    private function exploreTableStructure($tableName)
    {
        echo "=== ESTRUCTURA DE TABLA: {$tableName} ===\n";
        
        try {
            // Obtener columnas de la tabla
            $sql = "
                SELECT 
                    COLUMN_NAME,
                    DATA_TYPE,
                    CHARACTER_MAXIMUM_LENGTH,
                    IS_NULLABLE,
                    COLUMN_DEFAULT
                FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_SCHEMA + '.' + TABLE_NAME = ?
                ORDER BY ORDINAL_POSITION
            ";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$tableName]);
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($columns)) {
                echo "❌ No se encontraron columnas para la tabla {$tableName}\n";
                
                // Intentar listar todas las tablas disponibles
                echo "\n🔍 Buscando tablas disponibles...\n";
                $this->listAvailableTables();
            } else {
                echo "Columnas encontradas:\n";
                echo str_pad("COLUMNA", 30) . str_pad("TIPO", 20) . str_pad("LONGITUD", 12) . str_pad("NULO", 8) . "DEFAULT\n";
                echo str_repeat("-", 80) . "\n";
                
                foreach ($columns as $column) {
                    echo str_pad($column['COLUMN_NAME'], 30);
                    echo str_pad($column['DATA_TYPE'], 20);
                    echo str_pad($column['CHARACTER_MAXIMUM_LENGTH'] ?? 'N/A', 12);
                    echo str_pad($column['IS_NULLABLE'], 8);
                    echo $column['COLUMN_DEFAULT'] ?? 'NULL';
                    echo "\n";
                }
            }
        } catch (Exception $e) {
            echo "❌ Error explorando tabla: " . $e->getMessage() . "\n";
            
            // Intentar método alternativo
            echo "\n🔄 Intentando método alternativo...\n";
            $this->tryAlternativeTableExploration($tableName);
        }
        
        echo "\n";
    }

    private function exploreViewStructure($viewName)
    {
        echo "=== ESTRUCTURA DE VISTA: {$viewName} ===\n";
        
        try {
            // Obtener columnas de la vista
            $sql = "
                SELECT 
                    COLUMN_NAME,
                    DATA_TYPE,
                    CHARACTER_MAXIMUM_LENGTH,
                    IS_NULLABLE
                FROM INFORMATION_SCHEMA.COLUMNS 
                WHERE TABLE_SCHEMA + '.' + TABLE_NAME = ?
                ORDER BY ORDINAL_POSITION
            ";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$viewName]);
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($columns)) {
                echo "❌ No se encontraron columnas para la vista {$viewName}\n";
                
                // Intentar listar todas las vistas disponibles
                echo "\n🔍 Buscando vistas disponibles...\n";
                $this->listAvailableViews();
            } else {
                echo "Columnas encontradas:\n";
                echo str_pad("COLUMNA", 30) . str_pad("TIPO", 20) . str_pad("LONGITUD", 12) . "NULO\n";
                echo str_repeat("-", 70) . "\n";
                
                foreach ($columns as $column) {
                    echo str_pad($column['COLUMN_NAME'], 30);
                    echo str_pad($column['DATA_TYPE'], 20);
                    echo str_pad($column['CHARACTER_MAXIMUM_LENGTH'] ?? 'N/A', 12);
                    echo $column['IS_NULLABLE'];
                    echo "\n";
                }
            }
        } catch (Exception $e) {
            echo "❌ Error explorando vista: " . $e->getMessage() . "\n";
            
            // Intentar método alternativo
            echo "\n🔄 Intentando método alternativo...\n";
            $this->tryAlternativeViewExploration($viewName);
        }
        
        echo "\n";
    }

    private function listAvailableTables()
    {
        try {
            $sql = "
                SELECT 
                    SCHEMA_NAME(schema_id) + '.' + name as full_name,
                    name,
                    SCHEMA_NAME(schema_id) as schema_name
                FROM sys.tables 
                ORDER BY SCHEMA_NAME(schema_id), name
            ";
            
            $stmt = $this->pdo->query($sql);
            $tables = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "Tablas disponibles:\n";
            foreach ($tables as $table) {
                echo "- " . $table['full_name'] . "\n";
            }
        } catch (Exception $e) {
            echo "Error listando tablas: " . $e->getMessage() . "\n";
        }
    }

    private function listAvailableViews()
    {
        try {
            $sql = "
                SELECT 
                    SCHEMA_NAME(schema_id) + '.' + name as full_name,
                    name,
                    SCHEMA_NAME(schema_id) as schema_name
                FROM sys.views 
                ORDER BY SCHEMA_NAME(schema_id), name
            ";
            
            $stmt = $this->pdo->query($sql);
            $views = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo "Vistas disponibles:\n";
            foreach ($views as $view) {
                echo "- " . $view['full_name'] . "\n";
            }
        } catch (Exception $e) {
            echo "Error listando vistas: " . $e->getMessage() . "\n";
        }
    }

    private function tryAlternativeTableExploration($tableName)
    {
        try {
            // Método directo con sp_columns
            $sql = "EXEC sp_columns ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$tableName]);
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($columns)) {
                echo "Estructura alternativa encontrada:\n";
                foreach ($columns as $column) {
                    echo "- " . $column['COLUMN_NAME'] . " (" . $column['TYPE_NAME'] . ")\n";
                }
            }
        } catch (Exception $e) {
            echo "Método alternativo también falló: " . $e->getMessage() . "\n";
        }
    }

    private function tryAlternativeViewExploration($viewName)
    {
        try {
            // Método directo con sp_columns
            $sql = "EXEC sp_columns ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$viewName]);
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($columns)) {
                echo "Estructura alternativa encontrada:\n";
                foreach ($columns as $column) {
                    echo "- " . $column['COLUMN_NAME'] . " (" . $column['TYPE_NAME'] . ")\n";
                }
            }
        } catch (Exception $e) {
            echo "Método alternativo también falló: " . $e->getMessage() . "\n";
        }
    }

    private function showSampleData()
    {
        echo "=== DATOS DE MUESTRA ===\n";
        
        // Datos de muestra de ODS.TAB_MERCADO
        echo "\n--- Muestra de ODS.TAB_MERCADO (primeros 5 registros) ---\n";
        try {
            $stmt = $this->pdo->query("SELECT TOP 5 * FROM ODS.TAB_MERCADO");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($data)) {
                $this->printDataTable($data);
            } else {
                echo "No hay datos en ODS.TAB_MERCADO\n";
            }
        } catch (Exception $e) {
            echo "Error obteniendo datos de ODS.TAB_MERCADO: " . $e->getMessage() . "\n";
        }
        
        // Datos de muestra de DM.MERCADO
        echo "\n--- Muestra de DM.MERCADO (primeros 5 registros) ---\n";
        try {
            $stmt = $this->pdo->query("SELECT TOP 5 * FROM DM.MERCADO");
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (!empty($data)) {
                $this->printDataTable($data);
            } else {
                echo "No hay datos en DM.MERCADO\n";
            }
        } catch (Exception $e) {
            echo "Error obteniendo datos de DM.MERCADO: " . $e->getMessage() . "\n";
        }
    }

    private function printDataTable($data)
    {
        if (empty($data)) return;
        
        // Obtener nombres de columnas
        $columns = array_keys($data[0]);
        
        // Imprimir encabezados
        foreach ($columns as $column) {
            echo str_pad($column, 20);
        }
        echo "\n";
        echo str_repeat("-", count($columns) * 20) . "\n";
        
        // Imprimir datos
        foreach ($data as $row) {
            foreach ($row as $value) {
                echo str_pad(substr(strval($value), 0, 18), 20);
            }
            echo "\n";
        }
    }
}

// Ejecutar exploración
$explorer = new DatabaseExplorer();
$explorer->exploreDatabase();

echo "\n=== EXPLORACIÓN COMPLETADA ===\n";
echo "Con esta información podremos adaptar el código de mercados.\n";
