<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class StoredProcedureAuditService
{
    /**
     * Ejecutar SP_INSERT_MERCADO con auditoría automática
     */
    public static function createMarketWithAudit(
        string $marketName,
        ?string $module = 'market-management',
        ?string $controller = 'MarketManagementController'
    ): array {
        try {
            $user = Auth::user();
            $request = request();

            $result = DB::connection('sqlsrv')->select(
                'EXEC ODS.SP_INSERT_MERCADO_WITH_AUDIT ?, ?, ?, ?, ?, ?, ?, ?, ?',
                [
                    $marketName,                           // @mercado
                    $user ? $user->id : null,             // @idUsuario
                    $user ? $user->name : 'Sistema',      // @nombreUsuario
                    $user ? $user->email : null,          // @emailUsuario
                    $request ? $request->ip() : null,     // @ip
                    $request ? $request->userAgent() : null, // @userAgent
                    $request ? $request->session()->getId() : null, // @sesionId
                    $module,                               // @modulo
                    $controller                            // @controlador
                ]
            );

            if (!empty($result)) {
                $marketData = $result[0];
                return [
                    'success' => (bool)$marketData->exitoso,
                    'message' => $marketData->mensaje,
                    'data' => [
                        'idMercado' => $marketData->idMercado,
                        'mercado' => $marketData->mercado,
                        'solicitud' => $marketData->solicitud,
                        'fechaRegistro' => $marketData->fechaRegistro
                    ]
                ];
            }

            return [
                'success' => false,
                'message' => 'No se recibió respuesta del stored procedure',
                'data' => null
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al ejecutar stored procedure: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Ejecutar SP_UPDATE_MERCADO con auditoría automática
     */
    public static function updateMarketWithAudit(
        int $marketId,
        string $newName,
        ?string $module = 'market-management',
        ?string $controller = 'MarketManagementController'
    ): array {
        try {
            $user = Auth::user();
            $request = request();

            $result = DB::connection('sqlsrv')->select(
                'EXEC ODS.SP_UPDATE_MERCADO_WITH_AUDIT ?, ?, ?, ?, ?, ?, ?, ?, ?, ?',
                [
                    $marketId,                             // @idMercado
                    $newName,                              // @nuevoNombre
                    $user ? $user->id : null,             // @idUsuario
                    $user ? $user->name : 'Sistema',      // @nombreUsuario
                    $user ? $user->email : null,          // @emailUsuario
                    $request ? $request->ip() : null,     // @ip
                    $request ? $request->userAgent() : null, // @userAgent
                    $request ? $request->session()->getId() : null, // @sesionId
                    $module,                               // @modulo
                    $controller                            // @controlador
                ]
            );

            if (!empty($result)) {
                $marketData = $result[0];
                return [
                    'success' => (bool)$marketData->exitoso,
                    'message' => $marketData->mensaje,
                    'data' => [
                        'idMercado' => $marketData->idMercado,
                        'mercado' => $marketData->mercado,
                        'nombreAnterior' => $marketData->nombreAnterior ?? null
                    ]
                ];
            }

            return [
                'success' => false,
                'message' => 'No se recibió respuesta del stored procedure',
                'data' => null
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al ejecutar stored procedure: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Ejecutar cualquier stored procedure con auditoría básica
     */
    public static function executeWithAudit(
        string $storedProcedureName,
        array $parameters,
        string $table,
        ?string $description = null,
        ?string $module = null,
        ?string $controller = null
    ): array {
        try {
            $user = Auth::user();
            $request = request();
            $startTime = microtime(true);

            // Construir la consulta SQL dinámicamente
            $placeholders = str_repeat('?,', count($parameters));
            $placeholders = rtrim($placeholders, ',');
            
            $sql = "EXEC {$storedProcedureName} {$placeholders}";
            
            $result = DB::connection('sqlsrv')->select($sql, $parameters);
            
            $duration = round((microtime(true) - $startTime) * 1000);
            
            // Log manual usando AuditService
            AuditService::log(
                $storedProcedureName,
                $table,
                $description ?: "Ejecución de {$storedProcedureName}",
                null,
                null,
                ['parameters' => $parameters],
                $module,
                $controller,
                true,
                null,
                [
                    'stored_procedure' => $storedProcedureName,
                    'duration_ms' => $duration,
                    'result_count' => count($result)
                ]
            );

            return [
                'success' => true,
                'message' => 'Stored procedure ejecutado exitosamente',
                'data' => $result,
                'duration_ms' => $duration
            ];

        } catch (\Exception $e) {
            // Log del error
            AuditService::log(
                $storedProcedureName,
                $table,
                $description ?: "Error en {$storedProcedureName}",
                null,
                null,
                ['parameters' => $parameters],
                $module,
                $controller,
                false,
                $e->getMessage(),
                [
                    'stored_procedure' => $storedProcedureName,
                    'error_type' => get_class($e)
                ]
            );

            return [
                'success' => false,
                'message' => 'Error al ejecutar stored procedure: ' . $e->getMessage(),
                'data' => null
            ];
        }
    }

    /**
     * Obtener logs de auditoría usando el SP de consulta
     */
    public static function getAuditLogs(
        ?int $userId = null,
        ?string $action = null,
        ?string $table = null,
        ?string $module = null,
        ?string $startDate = null,
        ?string $endDate = null,
        int $pageSize = 50,
        int $pageNumber = 1
    ): array {
        try {
            $result = DB::connection('sqlsrv')->select(
                'EXEC ODS.SP_GET_AUDIT_LOGS ?, ?, ?, ?, ?, ?, ?, ?',
                [
                    $userId,
                    $action,
                    $table,
                    $module,
                    $startDate,
                    $endDate,
                    $pageSize,
                    $pageNumber
                ]
            );

            // El SP retorna dos result sets: los logs y el total count
            return [
                'success' => true,
                'logs' => $result,
                'pagination' => [
                    'page' => $pageNumber,
                    'pageSize' => $pageSize
                ]
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al consultar logs de auditoría: ' . $e->getMessage(),
                'logs' => []
            ];
        }
    }

    /**
     * Validar que un stored procedure existe antes de ejecutarlo
     */
    public static function storedProcedureExists(string $procedureName): bool
    {
        try {
            $result = DB::connection('sqlsrv')->select(
                "SELECT COUNT(*) as count 
                FROM INFORMATION_SCHEMA.ROUTINES 
                WHERE ROUTINE_TYPE = 'PROCEDURE' 
                AND ROUTINE_NAME = ?",
                [$procedureName]
            );

            return $result[0]->count > 0;

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Obtener estadísticas de stored procedures más ejecutados
     */
    public static function getStoredProcedureStats(?string $startDate = null, ?string $endDate = null): array
    {
        try {
            $whereClause = '';
            $params = [];

            if ($startDate && $endDate) {
                $whereClause = 'WHERE fechaHora BETWEEN ? AND ?';
                $params = [$startDate, $endDate];
            }

            $result = DB::connection('sqlsrv')->select(
                "SELECT 
                    accion as stored_procedure,
                    COUNT(*) as execution_count,
                    SUM(CASE WHEN exitoso = 1 THEN 1 ELSE 0 END) as successful,
                    SUM(CASE WHEN exitoso = 0 THEN 1 ELSE 0 END) as failed,
                    AVG(duracionMs) as avg_duration_ms,
                    MIN(fechaHora) as first_execution,
                    MAX(fechaHora) as last_execution
                FROM ODS.TAB_AUDIT_LOGS 
                {$whereClause}
                AND accion LIKE 'SP_%'
                GROUP BY accion
                ORDER BY execution_count DESC",
                $params
            );

            return [
                'success' => true,
                'stats' => $result
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al obtener estadísticas: ' . $e->getMessage(),
                'stats' => []
            ];
        }
    }
}
