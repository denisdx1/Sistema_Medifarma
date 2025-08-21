<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UniversalAuditService
{
    /**
     * Ejecutar un stored procedure con auditoría automática
     */
    public static function executeStoredProcedure(
        string $procedureName,
        array $parameters = [],
        ?string $description = null
    ): array {
        try {
            $userId = Auth::id();
            
            // Establecer contexto de usuario
            if ($userId) {
                DB::connection('sqlsrv')->statement('EXEC ODS.SP_SET_USER_CONTEXT ?', [$userId]);
            }
            
            // Preparar parámetros como string para auditoría
            $paramString = empty($parameters) ? null : implode(', ', array_map(function($param) {
                return is_string($param) ? "'{$param}'" : $param;
            }, $parameters));
            
            // Construir SQL dinámico
            $placeholders = str_repeat('?,', count($parameters));
            $placeholders = rtrim($placeholders, ',');
            $sql = "EXEC {$procedureName} {$placeholders}";
            
            // Ejecutar con auditoría automática usando el wrapper universal
            $fullSql = "EXEC ODS.SP_EXECUTE_WITH_AUDIT '{$procedureName}', " . 
                      ($paramString ? "'{$paramString}'" : 'NULL') . 
                      ", '{$sql}'";
            
            DB::connection('sqlsrv')->statement($fullSql, $parameters);
            
            return [
                'success' => true,
                'message' => $description ?? "Stored procedure {$procedureName} ejecutado correctamente",
                'procedure' => $procedureName,
                'parameters' => $parameters
            ];
            
        } catch (\Exception $e) {
            // El error ya se registra automáticamente en el sistema de auditoría
            return [
                'success' => false,
                'message' => "Error ejecutando {$procedureName}: " . $e->getMessage(),
                'procedure' => $procedureName,
                'parameters' => $parameters,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Crear mercado con auditoría automática
     */
    public static function createMercado(string $nombreMercado): array
    {
        return self::executeStoredProcedure(
            'SP_INSERT_MERCADO',
            [$nombreMercado],
            "Creando nuevo mercado: {$nombreMercado}"
        );
    }
    
    /**
     * Actualizar mercado con auditoría automática
     */
    public static function updateMercado(int $idMercado, string $nombreMercado): array
    {
        return self::executeStoredProcedure(
            'SP_UPDATE_MERCADO',
            [$idMercado, $nombreMercado],
            "Actualizando mercado ID {$idMercado}: {$nombreMercado}"
        );
    }
    
    /**
     * Insertar configuración con auditoría automática
     */
    public static function insertConfiguracion(int $idMercado, string $codigo, string $fuente): array
    {
        return self::executeStoredProcedure(
            'SP_INSERT_CONFIGURACION',
            [$idMercado, $codigo, $fuente],
            "Asignando producto {$codigo} al mercado ID {$idMercado}"
        );
    }
    
    /**
     * Actualizar configuración con auditoría automática
     */
    public static function updateConfiguracion(string $codigo, string $fuente, int $idMercado): array
    {
        return self::executeStoredProcedure(
            'SP_UPDATE_CONFIGURACION',
            [$codigo, $fuente, $idMercado],
            "Moviendo producto {$codigo} al mercado ID {$idMercado}"
        );
    }
    
    /**
     * Asignar producto a RESTO con auditoría automática
     */
    public static function asignarResto(string $codigo): array
    {
        return self::executeStoredProcedure(
            'SP_ASIGNAR_RESTO',
            [$codigo],
            "Enviando producto {$codigo} a RESTO"
        );
    }
    
    /**
     * Obtener logs de auditoría completos
     */
    public static function getAuditLogs(
        ?int $userId = null,
        ?string $action = null,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        int $limit = 100
    ): array {
        try {
            $query = DB::connection('sqlsrv')
                ->table('ODS.VW_AUDIT_COMPLETE')
                ->select([
                    'idAuditLog',
                    'idUsuario',
                    'nombre_usuario',
                    'accion',
                    'stored_procedure',
                    'descripcion',
                    'fecha_hora',
                    'ip_address',
                    'resultado',
                    'mensaje_error',
                    'duration_ms',
                    'rows_affected'
                ])
                ->orderBy('fecha_hora', 'desc');
            
            // Aplicar filtros
            if ($userId) {
                $query->where('idUsuario', $userId);
            }
            
            if ($action) {
                $query->where('accion', 'LIKE', "%{$action}%");
            }
            
            if ($dateFrom) {
                $query->where('fecha_hora', '>=', $dateFrom);
            }
            
            if ($dateTo) {
                $query->where('fecha_hora', '<=', $dateTo);
            }
            
            $results = $query->limit($limit)->get();
            
            return [
                'success' => true,
                'data' => $results->toArray(),
                'count' => $results->count(),
                'filters' => [
                    'userId' => $userId,
                    'action' => $action,
                    'dateFrom' => $dateFrom,
                    'dateTo' => $dateTo,
                    'limit' => $limit
                ]
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error obteniendo logs de auditoría: ' . $e->getMessage(),
                'data' => [],
                'count' => 0
            ];
        }
    }
    
    /**
     * Obtener estadísticas de auditoría
     */
    public static function getAuditStats(?string $dateFrom = null, ?string $dateTo = null): array
    {
        try {
            $query = DB::connection('sqlsrv')
                ->table('ODS.VW_AUDIT_COMPLETE');
            
            if ($dateFrom) {
                $query->where('fecha_hora', '>=', $dateFrom);
            }
            
            if ($dateTo) {
                $query->where('fecha_hora', '<=', $dateTo);
            }
            
            // Estadísticas por acción
            $actionStats = (clone $query)
                ->select('accion')
                ->selectRaw('COUNT(*) as total')
                ->selectRaw('SUM(CASE WHEN resultado = \'EXITOSO\' THEN 1 ELSE 0 END) as exitosos')
                ->selectRaw('SUM(CASE WHEN resultado = \'ERROR\' THEN 1 ELSE 0 END) as errores')
                ->selectRaw('AVG(CAST(duration_ms AS FLOAT)) as duracion_promedio_ms')
                ->groupBy('accion')
                ->orderBy('total', 'desc')
                ->get();
            
            // Estadísticas por usuario
            $userStats = (clone $query)
                ->select('nombre_usuario', 'idUsuario')
                ->selectRaw('COUNT(*) as total_acciones')
                ->selectRaw('SUM(CASE WHEN resultado = \'ERROR\' THEN 1 ELSE 0 END) as errores')
                ->whereNotNull('nombre_usuario')
                ->groupBy('nombre_usuario', 'idUsuario')
                ->orderBy('total_acciones', 'desc')
                ->limit(10)
                ->get();
            
            // Totales generales
            $totals = $query
                ->selectRaw('COUNT(*) as total_logs')
                ->selectRaw('SUM(CASE WHEN resultado = \'EXITOSO\' THEN 1 ELSE 0 END) as total_exitosos')
                ->selectRaw('SUM(CASE WHEN resultado = \'ERROR\' THEN 1 ELSE 0 END) as total_errores')
                ->selectRaw('COUNT(DISTINCT idUsuario) as usuarios_activos')
                ->first();
            
            return [
                'success' => true,
                'totals' => $totals,
                'by_action' => $actionStats->toArray(),
                'by_user' => $userStats->toArray(),
                'period' => [
                    'from' => $dateFrom,
                    'to' => $dateTo
                ]
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error obteniendo estadísticas: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Limpiar logs antiguos
     */
    public static function cleanupOldLogs(int $daysToKeep = 90): array
    {
        try {
            DB::connection('sqlsrv')->statement('EXEC ODS.SP_CLEANUP_AUDIT_LOGS ?', [$daysToKeep]);
            
            return [
                'success' => true,
                'message' => "Limpieza de logs completada. Conservados últimos {$daysToKeep} días."
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error en limpieza de logs: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Registrar login manualmente
     */
    public static function logLogin(int $userId, bool $success = true, ?string $error = null): void
    {
        try {
            DB::connection('sqlsrv')->insert('
                INSERT INTO ODS.TAB_AUDIT_LOG (idUsuario, accion, descripcion, fecha_hora, ip_address, resultado, mensaje_error)
                VALUES (?, ?, ?, GETDATE(), ?, ?, ?)
            ', [
                $userId,
                $success ? 'LOGIN_SUCCESS' : 'LOGIN_FAILED',
                $success ? 'Inicio de sesión exitoso' : 'Intento fallido de inicio de sesión',
                request()->ip(),
                $success ? 'EXITOSO' : 'ERROR',
                $error
            ]);
        } catch (\Exception $e) {
            // Log error but don't fail
            \Log::error('Error logging login audit', ['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Registrar logout manualmente
     */
    public static function logLogout(): void
    {
        if (Auth::check()) {
            try {
                DB::connection('sqlsrv')->insert('
                    INSERT INTO ODS.TAB_AUDIT_LOG (idUsuario, accion, descripcion, fecha_hora, ip_address, resultado)
                    VALUES (?, ?, ?, GETDATE(), ?, ?)
                ', [
                    Auth::id(),
                    'LOGOUT',
                    'Cierre de sesión',
                    request()->ip(),
                    'EXITOSO'
                ]);
            } catch (\Exception $e) {
                // Log error but don't fail
                \Log::error('Error logging logout audit', ['error' => $e->getMessage()]);
            }
        }
    }
}
