<?php

/**
 * Explorar estructura de ODS.TAB_USUARIO
 */

require_once __DIR__ . '/vendor/autoload.php';
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

echo "=== EXPLORANDO ODS.TAB_USUARIO ===\n\n";

try {
    $dsn = "sqlsrv:Server={$_ENV['DB_HOST']},{$_ENV['DB_PORT']};Database={$_ENV['DB_DATABASE']}";
    $pdo = new PDO($dsn, $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 1. Estructura de la tabla
    echo "1. ESTRUCTURA DE ODS.TAB_USUARIO:\n";
    $sql = "
        SELECT 
            COLUMN_NAME as Columna,
            DATA_TYPE as Tipo,
            CHARACTER_MAXIMUM_LENGTH as Longitud,
            IS_NULLABLE as Permite_Nulo,
            COLUMN_DEFAULT as Valor_Default
        FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = 'ODS' AND TABLE_NAME = 'TAB_USUARIO'
        ORDER BY ORDINAL_POSITION
    ";
    
    $stmt = $pdo->query($sql);
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo str_pad("COLUMNA", 20) . str_pad("TIPO", 15) . str_pad("LONGITUD", 10) . str_pad("NULO", 8) . "DEFAULT\n";
    echo str_repeat("-", 70) . "\n";
    
    foreach ($columns as $column) {
        echo str_pad($column['Columna'], 20);
        echo str_pad($column['Tipo'], 15);
        echo str_pad($column['Longitud'] ?? 'N/A', 10);
        echo str_pad($column['Permite_Nulo'], 8);
        echo $column['Valor_Default'] ?? 'NULL';
        echo "\n";
    }
    
    // 2. Contar registros
    echo "\n2. TOTAL DE USUARIOS:\n";
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM ODS.TAB_USUARIO");
    $total = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "Total usuarios: {$total['total']}\n";
    
    // 3. Muestra de datos
    echo "\n3. MUESTRA DE USUARIOS (primeros 5):\n";
    $stmt = $pdo->query("SELECT TOP 5 * FROM ODS.TAB_USUARIO ORDER BY idUsuario");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($usuarios)) {
        $headers = array_keys($usuarios[0]);
        
        foreach ($headers as $header) {
            echo str_pad($header, 15);
        }
        echo "\n" . str_repeat("-", count($headers) * 15) . "\n";
        
        foreach ($usuarios as $usuario) {
            foreach ($usuario as $value) {
                $displayValue = is_null($value) ? 'NULL' : substr(strval($value), 0, 13);
                echo str_pad($displayValue, 15);
            }
            echo "\n";
        }
    }
    
    // 4. Verificar roles disponibles
    echo "\n4. ROLES DISPONIBLES:\n";
    $stmt = $pdo->query("SELECT DISTINCT idRol, COUNT(*) as cantidad FROM ODS.TAB_USUARIO GROUP BY idRol ORDER BY idRol");
    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($roles as $rol) {
        echo "Rol ID: {$rol['idRol']} - Usuarios: {$rol['cantidad']}\n";
    }
    
    // 5. Estados disponibles
    echo "\n5. ESTADOS DISPONIBLES:\n";
    $stmt = $pdo->query("SELECT DISTINCT idEstado, COUNT(*) as cantidad FROM ODS.TAB_USUARIO GROUP BY idEstado ORDER BY idEstado");
    $estados = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($estados as $estado) {
        $estadoTexto = $estado['idEstado'] == 1 ? 'Activo' : 'Inactivo';
        echo "Estado ID: {$estado['idEstado']} ({$estadoTexto}) - Usuarios: {$estado['cantidad']}\n";
    }
    
    // 6. Buscar claves primarias
    echo "\n6. CLAVES PRIMARIAS:\n";
    $sql = "
        SELECT COLUMN_NAME 
        FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
        WHERE TABLE_SCHEMA = 'ODS' 
        AND TABLE_NAME = 'TAB_USUARIO'
        AND CONSTRAINT_NAME LIKE 'PK_%'
    ";
    $stmt = $pdo->query($sql);
    $pks = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (!empty($pks)) {
        foreach ($pks as $pk) {
            echo "Clave primaria: {$pk['COLUMN_NAME']}\n";
        }
    } else {
        echo "No se encontraron claves primarias definidas.\n";
    }
    
    echo "\n=== EXPLORACIÓN COMPLETADA ===\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}
