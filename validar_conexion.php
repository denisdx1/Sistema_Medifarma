<?php

/**
 * Script para validar conexiones de base de datos
 * Autor: Sistema Medifarma
 * Fecha: 2025-08-19
 */

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use Dotenv\Dotenv;

// Cargar variables de entorno
$dotenv = Dotenv::createImmutable(__DIR__);
$dotenv->load();

class DatabaseConnectionValidator
{
    private $connections = [];

    public function __construct()
    {
        echo "=== VALIDADOR DE CONEXIONES DE BASE DE DATOS ===\n\n";
    }

    /**
     * Agregar una conexión para validar
     */
    public function addConnection($name, $config)
    {
        $this->connections[$name] = $config;
    }

    /**
     * Validar todas las conexiones agregadas
     */
    public function validateAllConnections()
    {
        foreach ($this->connections as $name => $config) {
            $this->validateConnection($name, $config);
        }
    }

    /**
     * Validar una conexión específica
     */
    public function validateConnection($name, $config)
    {
        echo "=== Validando conexión: {$name} ===\n";
        echo "Host: {$config['host']}:{$config['port']}\n";
        echo "Base de datos: {$config['database']}\n";
        echo "Usuario: {$config['username']}\n\n";

        try {
            // Configurar Capsule
            $capsule = new Capsule;
            $capsule->addConnection($config, $name);
            $capsule->setAsGlobal();
            $capsule->bootEloquent();

            // Intentar conexión
            $connection = $capsule->getConnection($name);
            $pdo = $connection->getPdo();

            if ($pdo) {
                echo "✅ CONEXIÓN EXITOSA\n";
                
                // Obtener información del servidor
                $this->getServerInfo($connection, $config['driver']);
                
                // Probar una consulta simple
                $this->testSimpleQuery($connection, $config['driver']);
                
            } else {
                echo "❌ ERROR: No se pudo establecer la conexión\n";
            }

        } catch (Exception $e) {
            echo "❌ ERROR DE CONEXIÓN:\n";
            echo "Tipo: " . get_class($e) . "\n";
            echo "Mensaje: " . $e->getMessage() . "\n";
            
            // Diagnósticos adicionales
            $this->runDiagnostics($config);
        }

        echo "\n" . str_repeat("-", 60) . "\n\n";
    }

    /**
     * Obtener información del servidor
     */
    private function getServerInfo($connection, $driver)
    {
        try {
            switch ($driver) {
                case 'sqlsrv':
                    $result = $connection->select("SELECT @@VERSION as version, @@SERVERNAME as server_name");
                    if (!empty($result)) {
                        echo "Servidor: " . $result[0]->server_name . "\n";
                        echo "Versión: " . substr($result[0]->version, 0, 100) . "...\n";
                    }
                    break;
                    
                case 'mysql':
                case 'mariadb':
                    $result = $connection->select("SELECT VERSION() as version");
                    if (!empty($result)) {
                        echo "Versión MySQL/MariaDB: " . $result[0]->version . "\n";
                    }
                    break;
                    
                case 'pgsql':
                    $result = $connection->select("SELECT version()");
                    if (!empty($result)) {
                        echo "Versión PostgreSQL: " . $result[0]->version . "\n";
                    }
                    break;
            }
        } catch (Exception $e) {
            echo "⚠️ No se pudo obtener información del servidor: " . $e->getMessage() . "\n";
        }
    }

    /**
     * Probar una consulta simple
     */
    private function testSimpleQuery($connection, $driver)
    {
        try {
            switch ($driver) {
                case 'sqlsrv':
                    $result = $connection->select("SELECT GETDATE() as fecha_actual");
                    break;
                case 'mysql':
                case 'mariadb':
                    $result = $connection->select("SELECT NOW() as fecha_actual");
                    break;
                case 'pgsql':
                    $result = $connection->select("SELECT NOW() as fecha_actual");
                    break;
                default:
                    $result = $connection->select("SELECT 1 as test");
            }

            if (!empty($result)) {
                echo "✅ Consulta de prueba exitosa\n";
                if (isset($result[0]->fecha_actual)) {
                    echo "Fecha del servidor: " . $result[0]->fecha_actual . "\n";
                }
            }
        } catch (Exception $e) {
            echo "⚠️ Error en consulta de prueba: " . $e->getMessage() . "\n";
        }
    }

    /**
     * Ejecutar diagnósticos de red y configuración
     */
    private function runDiagnostics($config)
    {
        echo "\n🔍 DIAGNÓSTICOS:\n";
        
        // Verificar si el host es alcanzable
        echo "- Verificando conectividad de red...\n";
        $host = $config['host'];
        $port = $config['port'];
        
        if (function_exists('fsockopen')) {
            $connection = @fsockopen($host, $port, $errno, $errstr, 5);
            if ($connection) {
                echo "  ✅ El host {$host}:{$port} es alcanzable\n";
                fclose($connection);
            } else {
                echo "  ❌ No se puede conectar a {$host}:{$port} (Error: {$errno} - {$errstr})\n";
            }
        }

        // Verificar extensiones PHP
        echo "- Verificando extensiones PHP...\n";
        $driver = $config['driver'];
        switch ($driver) {
            case 'sqlsrv':
                if (extension_loaded('pdo_sqlsrv')) {
                    echo "  ✅ Extensión pdo_sqlsrv cargada\n";
                } else {
                    echo "  ❌ Extensión pdo_sqlsrv NO está cargada\n";
                }
                break;
            case 'mysql':
            case 'mariadb':
                if (extension_loaded('pdo_mysql')) {
                    echo "  ✅ Extensión pdo_mysql cargada\n";
                } else {
                    echo "  ❌ Extensión pdo_mysql NO está cargada\n";
                }
                break;
            case 'pgsql':
                if (extension_loaded('pdo_pgsql')) {
                    echo "  ✅ Extensión pdo_pgsql cargada\n";
                } else {
                    echo "  ❌ Extensión pdo_pgsql NO está cargada\n";
                }
                break;
        }
    }

    /**
     * Mostrar configuración actual del .env
     */
    public function showCurrentConfig()
    {
        echo "=== CONFIGURACIÓN ACTUAL (.env) ===\n";
        echo "DB_CONNECTION: " . ($_ENV['DB_CONNECTION'] ?? 'No definido') . "\n";
        echo "DB_HOST: " . ($_ENV['DB_HOST'] ?? 'No definido') . "\n";
        echo "DB_PORT: " . ($_ENV['DB_PORT'] ?? 'No definido') . "\n";
        echo "DB_DATABASE: " . ($_ENV['DB_DATABASE'] ?? 'No definido') . "\n";
        echo "DB_USERNAME: " . ($_ENV['DB_USERNAME'] ?? 'No definido') . "\n";
        echo "DB_PASSWORD: " . (empty($_ENV['DB_PASSWORD']) ? 'No definido' : '[CONFIGURADO]') . "\n";
        echo "\n" . str_repeat("-", 60) . "\n\n";
    }
}

// ========================================
// CONFIGURACIÓN DE CONEXIONES A VALIDAR
// ========================================

$validator = new DatabaseConnectionValidator();

// Mostrar configuración actual
$validator->showCurrentConfig();

// 1. Conexión actual (desde .env)
$validator->addConnection('actual', [
    'driver' => $_ENV['DB_CONNECTION'] ?? 'sqlsrv',
    'host' => $_ENV['DB_HOST'] ?? '172.16.8.221',
    'port' => $_ENV['DB_PORT'] ?? '50107',
    'database' => $_ENV['DB_DATABASE'] ?? 'DTM_MERCADOS',
    'username' => $_ENV['DB_USERNAME'] ?? 'druizp',
    'password' => $_ENV['DB_PASSWORD'] ?? '#BI_RUIZ2025',
    'charset' => 'utf8',
    'prefix' => '',
]);

// 2. NUEVA CONEXIÓN - Modifica estos valores según tu nueva base de datos
$validator->addConnection('nueva', [
    'driver' => 'sqlsrv',  // Cambia según tu driver: sqlsrv, mysql, pgsql
    'host' => '172.16.8.222',  // 🔧 CAMBIA ESTA IP
    'port' => '1433',  // 🔧 CAMBIA ESTE PUERTO
    'database' => 'NUEVA_BD',  // 🔧 CAMBIA ESTE NOMBRE DE BD
    'username' => 'nuevo_usuario',  // 🔧 CAMBIA ESTE USUARIO
    'password' => 'nueva_contraseña',  // 🔧 CAMBIA ESTA CONTRASEÑA
    'charset' => 'utf8',
    'prefix' => '',
]);

// 3. Ejemplo de conexión MySQL/MariaDB (opcional)
/*
$validator->addConnection('mysql_ejemplo', [
    'driver' => 'mysql',
    'host' => '127.0.0.1',
    'port' => '3306',
    'database' => 'mi_base_mysql',
    'username' => 'usuario_mysql',
    'password' => 'contraseña_mysql',
    'charset' => 'utf8mb4',
    'prefix' => '',
]);
*/

// ========================================
// EJECUTAR VALIDACIONES
// ========================================

echo "Iniciando validación de conexiones...\n";
echo "Fecha: " . date('Y-m-d H:i:s') . "\n\n";

$validator->validateAllConnections();

echo "=== VALIDACIÓN COMPLETADA ===\n";
echo "Si necesitas modificar las conexiones, edita este archivo y cambia los valores marcados con 🔧\n";
