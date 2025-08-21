<?php

namespace App\Services;

// Servicio eliminado por solicitud del usuario. Contenía lógica de logs y auditoría.
?>
    /**
     * Registrar un log de auditoría simple
     */
    public static function log(string $nombreUsuario, string $accion, string $descripcion): void
    {
        try {
            DB::connection('sqlsrv')->statement('
                EXEC ODS.SP_INSERT_AUDIT_LOG_SIMPLE 
                    @nombreUsuario = ?, 
                    @accion = ?, 
                    @descripcion = ?
            ', [
                $nombreUsuario,
                $accion,
                $descripcion
            ]);
        } catch (\Exception $e) {
            Log::error('Error al registrar auditoría: ' . $e->getMessage(), [
                'usuario' => $nombreUsuario,
                'accion' => $accion,
                'descripcion' => $descripcion
            ]);
        }
    }

    /**
     * Crear mercado con auditoría
     */
    public static function createMercado(string $mercado, string $nombreUsuario): array
    {
        try {
            $result = DB::connection('sqlsrv')->select('
                EXEC ODS.SP_INSERT_MERCADO_WITH_SIMPLE_AUDIT 
                    @mercado = ?, 
                    @nombreUsuario = ?
            ', [$mercado, $nombreUsuario]);

            return [
                'success' => true,
                'data' => $result[0] ?? null,
                'message' => 'Mercado creado exitosamente'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Actualizar mercado con auditoría
     */
    public static function updateMercado(int $idMercado, string $nuevoNombre, string $nombreUsuario): array
    {
        try {
            $result = DB::connection('sqlsrv')->select('
                EXEC ODS.SP_UPDATE_MERCADO_WITH_SIMPLE_AUDIT 
                    @idMercado = ?, 
                    @nuevoNombre = ?, 
                    @nombreUsuario = ?
            ', [$idMercado, $nuevoNombre, $nombreUsuario]);

            return [
                'success' => true,
                'data' => $result[0] ?? null,
                'message' => 'Mercado actualizado exitosamente'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Aprobar mercado con auditoría
     */
    public static function approveMercado(int $idMercado, string $nombreUsuario): array
    {
        try {
            $result = DB::connection('sqlsrv')->select('
                EXEC ODS.SP_APPROVE_MERCADO_WITH_SIMPLE_AUDIT 
                    @idMercado = ?, 
                    @nombreUsuario = ?
            ', [$idMercado, $nombreUsuario]);

            return [
                'success' => true,
                'data' => $result[0] ?? null,
                'message' => 'Mercado aprobado exitosamente'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'data' => null,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Obtener logs de auditoría
     */
    public static function getLogs(
        ?string $nombreUsuario = null,
        ?string $fechaInicio = null,
        ?string $fechaFin = null,
        int $pageSize = 50,
        int $pageNumber = 1
    ): array {
        try {
            $result = DB::connection('sqlsrv')->select('
                EXEC ODS.SP_GET_AUDIT_LOGS_SIMPLE 
                    @nombreUsuario = ?, 
                    @fechaInicio = ?, 
                    @fechaFin = ?,
                    @pageSize = ?,
                    @pageNumber = ?
            ', [
                $nombreUsuario,
                $fechaInicio,
                $fechaFin,
                $pageSize,
                $pageNumber
            ]);

            return [
                'success' => true,
                'data' => $result,
                'message' => 'Logs obtenidos exitosamente'
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'data' => [],
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Métodos de auditoría específicos para acciones comunes
     */
    public static function logLogin(string $nombreUsuario): void
    {
        self::log($nombreUsuario, 'INICIO SESIÓN', 'Usuario inició sesión en el sistema');
    }

    public static function logLogout(string $nombreUsuario): void
    {
        self::log($nombreUsuario, 'CERRAR SESIÓN', 'Usuario cerró sesión del sistema');
    }

    public static function logView(string $nombreUsuario, string $vista): void
    {
        self::log($nombreUsuario, 'VER', "Usuario accedió a la vista: {$vista}");
    }

    public static function logSearch(string $nombreUsuario, string $criterio): void
    {
        self::log($nombreUsuario, 'BUSCAR', "Usuario realizó búsqueda: {$criterio}");
    }

    public static function logExport(string $nombreUsuario, string $tipo): void
    {
        self::log($nombreUsuario, 'EXPORTAR', "Usuario exportó datos: {$tipo}");
    }
}
