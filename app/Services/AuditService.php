<?php

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AuditService
{
    /**
     * Crear un log de auditoría
     */
    public static function log(
        string $action,
        string $table,
        ?string $description = null,
        ?string $recordId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $module = null,
        ?string $controller = null,
        bool $successful = true,
        ?string $errorMessage = null,
        ?array $metadata = null
    ): bool {
        try {
            $user = Auth::user();
            $request = request();

            $logData = [
                'idUsuario' => $user ? $user->id : null,
                'nombreUsuario' => $user ? $user->name : 'Sistema',
                'emailUsuario' => $user ? $user->email : null,
                'accion' => strtoupper($action),
                'tabla' => $table,
                'descripcion' => $description,
                'registroId' => $recordId,
                'valoresAnteriores' => $oldValues ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null,
                'valoresNuevos' => $newValues ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null,
                'ip' => self::getClientIp($request),
                'userAgent' => $request ? $request->userAgent() : null,
                'sesionId' => $request ? $request->session()->getId() : null,
                'exitoso' => $successful,
                'mensajeError' => $errorMessage,
                'modulo' => $module,
                'controlador' => $controller,
                'metadatos' => $metadata ? json_encode($metadata, JSON_UNESCAPED_UNICODE) : null,
            ];

            return AuditLog::createLog($logData) !== false;

        } catch (\Exception $e) {
            \Log::error('Error in AuditService::log: ' . $e->getMessage(), [
                'action' => $action,
                'table' => $table,
                'description' => $description
            ]);
            return false;
        }
    }

    /**
     * Log de ejecución de stored procedure
     */
    public static function logStoredProcedure(
        string $storedProcedureName,
        array $parameters,
        string $table,
        ?string $recordId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $description = null,
        ?string $module = null,
        ?string $controller = null,
        bool $successful = true,
        ?string $errorMessage = null,
        ?array $metadata = null
    ): bool {
        $enhancedMetadata = array_merge($metadata ?? [], [
            'stored_procedure' => $storedProcedureName,
            'sp_parameters' => $parameters
        ]);

        return self::log(
            $storedProcedureName, // Usar el nombre del SP como acción
            $table,
            $description ?: "Ejecución de stored procedure {$storedProcedureName}",
            $recordId,
            $oldValues,
            $newValues,
            $module,
            $controller,
            $successful,
            $errorMessage,
            $enhancedMetadata
        );
    }

    /**
     * Log específico para SP_INSERT_MERCADO
     */
    public static function logCreateMarket(
        string $marketName,
        ?int $marketId = null,
        bool $successful = true,
        ?string $errorMessage = null,
        ?string $module = 'market-management',
        ?string $controller = 'MarketManagementController'
    ): bool {
        return self::logStoredProcedure(
            'SP_INSERT_MERCADO',
            ['mercado' => $marketName],
            'TAB_MERCADO',
            $marketId ? (string)$marketId : null,
            null,
            $successful ? [
                'mercado' => $marketName,
                'solicitud' => 'ESPERA',
                'estado' => 'ACTIVO'
            ] : null,
            $successful ? 'Creación exitosa de mercado' : 'Error al crear mercado',
            $module,
            $controller,
            $successful,
            $errorMessage
        );
    }

    /**
     * Log específico para actualización de mercados
     */
    public static function logUpdateMarket(
        int $marketId,
        string $oldName,
        string $newName,
        bool $successful = true,
        ?string $errorMessage = null,
        ?string $module = 'market-management',
        ?string $controller = 'MarketManagementController'
    ): bool {
        return self::logStoredProcedure(
            'SP_UPDATE_MERCADO',
            ['idMercado' => $marketId, 'nuevoNombre' => $newName],
            'TAB_MERCADO',
            (string)$marketId,
            ['mercado' => $oldName],
            ['mercado' => $newName],
            $successful ? 'Actualización exitosa de mercado' : 'Error al actualizar mercado',
            $module,
            $controller,
            $successful,
            $errorMessage
        );
    }

    /**
     * Log específico para aprobación de mercados
     */
    public static function logApproveMarket(
        int $marketId,
        string $marketName,
        bool $successful = true,
        ?string $errorMessage = null,
        ?string $module = 'market-administration',
        ?string $controller = 'MarketAdministrationController'
    ): bool {
        return self::logStoredProcedure(
            'SP_APPROVE_MERCADO',
            ['idMercado' => $marketId],
            'TAB_MERCADO',
            (string)$marketId,
            ['solicitud' => 'ESPERA'],
            ['solicitud' => 'APROBADO'],
            $successful ? "Aprobación exitosa del mercado: {$marketName}" : 'Error al aprobar mercado',
            $module,
            $controller,
            $successful,
            $errorMessage
        );
    }
    public static function logInsert(
        string $table,
        string $recordId,
        array $newValues,
        ?string $description = null,
        ?string $module = null,
        ?string $controller = null
    ): bool {
        return self::log(
            'INSERT',
            $table,
            $description ?: "Inserción de registro en {$table}",
            $recordId,
            null,
            $newValues,
            $module,
            $controller
        );
    }

    /**
     * Log de actualización de datos
     */
    public static function logUpdate(
        string $table,
        string $recordId,
        array $oldValues,
        array $newValues,
        ?string $description = null,
        ?string $module = null,
        ?string $controller = null
    ): bool {
        return self::log(
            'UPDATE',
            $table,
            $description ?: "Actualización de registro en {$table}",
            $recordId,
            $oldValues,
            $newValues,
            $module,
            $controller
        );
    }

    /**
     * Log de eliminación de datos
     */
    public static function logDelete(
        string $table,
        string $recordId,
        array $oldValues,
        ?string $description = null,
        ?string $module = null,
        ?string $controller = null
    ): bool {
        return self::log(
            'DELETE',
            $table,
            $description ?: "Eliminación de registro en {$table}",
            $recordId,
            $oldValues,
            null,
            $module,
            $controller
        );
    }

    /**
     * Log de inicio de sesión
     */
    public static function logLogin(
        string $email,
        bool $successful = true,
        ?string $errorMessage = null
    ): bool {
        return self::log(
            'LOGIN',
            'users',
            $successful ? 'Inicio de sesión exitoso' : 'Intento de inicio de sesión fallido',
            null,
            null,
            ['email' => $email],
            'auth',
            'AuthController',
            $successful,
            $errorMessage
        );
    }

    /**
     * Log de cierre de sesión
     */
    public static function logLogout(): bool
    {
        $user = Auth::user();
        return self::log(
            'LOGOUT',
            'users',
            'Cierre de sesión',
            $user ? (string)$user->id : null,
            null,
            $user ? ['email' => $user->email] : null,
            'auth',
            'AuthController'
        );
    }

    /**
     * Log de vista/consulta de datos
     */
    public static function logView(
        string $table,
        ?string $description = null,
        ?string $module = null,
        ?string $controller = null,
        ?array $metadata = null
    ): bool {
        return self::log(
            'VIEW',
            $table,
            $description ?: "Consulta de datos en {$table}",
            null,
            null,
            null,
            $module,
            $controller,
            true,
            null,
            $metadata
        );
    }

    /**
     * Log de error en operación
     */
    public static function logError(
        string $action,
        string $table,
        string $errorMessage,
        ?string $description = null,
        ?string $module = null,
        ?string $controller = null,
        ?array $metadata = null
    ): bool {
        return self::log(
            $action,
            $table,
            $description ?: "Error en operación {$action} en {$table}",
            null,
            null,
            null,
            $module,
            $controller,
            false,
            $errorMessage,
            $metadata
        );
    }

    /**
     * Obtener la IP real del cliente
     */
    private static function getClientIp(?Request $request = null): ?string
    {
        if (!$request) {
            return null;
        }

        $ipKeys = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        ];

        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                $ip = $_SERVER[$key];
                if (strpos($ip, ',') !== false) {
                    $ip = explode(',', $ip)[0];
                }
                $ip = trim($ip);
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        return $request->ip();
    }

    /**
     * Obtener estadísticas de auditoría
     */
    public static function getStatistics(?string $startDate = null, ?string $endDate = null): array
    {
        return AuditLog::getStats($startDate, $endDate);
    }

    /**
     * Obtener logs recientes
     */
    public static function getRecentLogs(int $limit = 20): \Illuminate\Database\Eloquent\Collection
    {
        return AuditLog::with('user')
            ->orderBy('fechaHora', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Limpiar logs antiguos
     */
    public static function cleanOldLogs(int $daysToKeep = 90): int
    {
        $cutoffDate = now()->subDays($daysToKeep);
        
        return AuditLog::where('fechaHora', '<', $cutoffDate)->delete();
    }
}
