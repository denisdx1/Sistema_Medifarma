<?php

namespace App\Traits;

// Trait eliminado por solicitud del usuario. Contenía lógica de logs y auditoría.
?>mespace App\Traits;

use App\Services\AuditService;
use Illuminate\Http\Request;

trait HasAuditTrail
{
    /**
     * Log an audit trail automatically
     */
    protected function auditLog(
        string $action,
        string $table,
        ?string $description = null,
        $recordId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        bool $successful = true,
        ?string $errorMessage = null,
        ?array $metadata = null
    ): bool {
        $module = $this->getModuleName();
        $controller = class_basename(static::class);

        return AuditService::log(
            $action,
            $table,
            $description,
            $recordId ? (string)$recordId : null,
            $oldValues,
            $newValues,
            $module,
            $controller,
            $successful,
            $errorMessage,
            $metadata
        );
    }

    /**
     * Log stored procedure execution
     */
    protected function auditStoredProcedure(
        string $storedProcedureName,
        array $parameters,
        string $table,
        ?string $description = null,
        $recordId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        bool $successful = true,
        ?string $errorMessage = null,
        ?array $metadata = null
    ): bool {
        $module = $this->getModuleName();
        $controller = class_basename(static::class);

        $enhancedMetadata = array_merge($metadata ?? [], [
            'stored_procedure' => $storedProcedureName,
            'sp_parameters' => $parameters
        ]);

        return $this->auditLog(
            $storedProcedureName,
            $table,
            $description ?: "Ejecución de SP: {$storedProcedureName}",
            $recordId,
            $oldValues,
            $newValues,
            $successful,
            $errorMessage,
            $enhancedMetadata
        );
    }

    /**
     * Log SP_INSERT_MERCADO execution
     */
    protected function auditCreateMarketSP(
        string $marketName,
        ?int $marketId = null,
        bool $successful = true,
        ?string $errorMessage = null
    ): bool {
        return $this->auditStoredProcedure(
            'SP_INSERT_MERCADO',
            ['mercado' => $marketName],
            'TAB_MERCADO',
            $successful ? 'Creación exitosa de mercado via SP' : 'Error al crear mercado via SP',
            $marketId ? (string)$marketId : null,
            null,
            $successful ? [
                'mercado' => $marketName,
                'solicitud' => 'ESPERA',
                'estado' => 'ACTIVO'
            ] : null,
            $successful,
            $errorMessage
        );
    }

    /**
     * Log market update via stored procedure
     */
    protected function auditUpdateMarketSP(
        int $marketId,
        string $oldName,
        string $newName,
        bool $successful = true,
        ?string $errorMessage = null
    ): bool {
        return $this->auditStoredProcedure(
            'SP_UPDATE_MERCADO',
            ['idMercado' => $marketId, 'nuevoNombre' => $newName],
            'TAB_MERCADO',
            $successful ? 'Actualización exitosa de mercado via SP' : 'Error al actualizar mercado via SP',
            (string)$marketId,
            ['mercado' => $oldName],
            ['mercado' => $newName],
            $successful,
            $errorMessage
        );
    }

    /**
     * Log market approval via stored procedure
     */
    protected function auditApproveMarketSP(
        int $marketId,
        string $marketName,
        bool $successful = true,
        ?string $errorMessage = null
    ): bool {
        return $this->auditStoredProcedure(
            'SP_APPROVE_MERCADO',
            ['idMercado' => $marketId],
            'TAB_MERCADO',
            $successful ? "Aprobación exitosa del mercado: {$marketName}" : 'Error al aprobar mercado',
            (string)$marketId,
            ['solicitud' => 'ESPERA'],
            ['solicitud' => 'APROBADO'],
            $successful,
            $errorMessage
        );
    }
    protected function auditCreate(string $table, $recordId, array $data, ?string $description = null): bool
    {
        return $this->auditLog(
            'INSERT',
            $table,
            $description ?: "Creación de registro en {$table}",
            $recordId,
            null,
            $data
        );
    }

    /**
     * Log update operation
     */
    protected function auditUpdate(
        string $table, 
        $recordId, 
        array $oldData, 
        array $newData, 
        ?string $description = null
    ): bool {
        return $this->auditLog(
            'UPDATE',
            $table,
            $description ?: "Actualización de registro en {$table}",
            $recordId,
            $oldData,
            $newData
        );
    }

    /**
     * Log delete operation
     */
    protected function auditDelete(string $table, $recordId, array $data, ?string $description = null): bool
    {
        return $this->auditLog(
            'DELETE',
            $table,
            $description ?: "Eliminación de registro en {$table}",
            $recordId,
            $data,
            null
        );
    }

    /**
     * Log view operation
     */
    protected function auditView(string $table, ?string $description = null, ?array $metadata = null): bool
    {
        return $this->auditLog(
            'VIEW',
            $table,
            $description ?: "Consulta de datos en {$table}",
            null,
            null,
            null,
            true,
            null,
            $metadata
        );
    }

    /**
     * Log error operation
     */
    protected function auditError(
        string $action,
        string $table,
        string $errorMessage,
        ?string $description = null,
        ?array $metadata = null
    ): bool {
        return $this->auditLog(
            $action,
            $table,
            $description ?: "Error en operación {$action}",
            null,
            null,
            null,
            false,
            $errorMessage,
            $metadata
        );
    }

    /**
     * Get module name based on controller
     */
    protected function getModuleName(): string
    {
        $className = class_basename(static::class);
        
        $moduleMap = [
            'MarketManagementController' => 'market-management',
            'MarketConfigurationController' => 'market-configuration',
            'MarketAdministrationController' => 'market-administration',
            'UserManagementController' => 'user-management',
            'AuthController' => 'auth',
            'DashboardController' => 'dashboard',
        ];

        return $moduleMap[$className] ?? strtolower(str_replace('Controller', '', $className));
    }

    /**
     * Start timing for performance audit
     */
    protected function startTiming(): float
    {
        return microtime(true);
    }

    /**
     * Calculate duration in milliseconds
     */
    protected function calculateDuration(float $startTime): int
    {
        return round((microtime(true) - $startTime) * 1000);
    }

    /**
     * Log with timing information
     */
    protected function auditLogWithTiming(
        float $startTime,
        string $action,
        string $table,
        ?string $description = null,
        $recordId = null,
        ?array $oldValues = null,
        ?array $newValues = null,
        bool $successful = true,
        ?string $errorMessage = null,
        ?array $metadata = null
    ): bool {
        $duration = $this->calculateDuration($startTime);
        
        $enhancedMetadata = array_merge($metadata ?? [], [
            'duration_ms' => $duration,
            'performance_category' => $this->categorizeDuration($duration)
        ]);

        return $this->auditLog(
            $action,
            $table,
            $description,
            $recordId,
            $oldValues,
            $newValues,
            $successful,
            $errorMessage,
            $enhancedMetadata
        );
    }

    /**
     * Categorize duration for performance analysis
     */
    protected function categorizeDuration(int $durationMs): string
    {
        if ($durationMs < 100) return 'fast';
        if ($durationMs < 500) return 'normal';
        if ($durationMs < 2000) return 'slow';
        return 'very_slow';
    }

    /**
     * Sanitize data for logging (remove sensitive information)
     */
    protected function sanitizeForAudit(array $data): array
    {
        $sensitiveFields = [
            'password',
            'password_confirmation',
            'token',
            'api_key',
            '_token',
            'csrf_token'
        ];

        $sanitized = $data;
        
        foreach ($sensitiveFields as $field) {
            if (isset($sanitized[$field])) {
                $sanitized[$field] = '[HIDDEN]';
            }
        }

        return $sanitized;
    }

    /**
     * Extract only changed fields for update logging
     */
    protected function getChangedFields(array $original, array $updated): array
    {
        $changes = [];
        
        foreach ($updated as $key => $value) {
            if (!array_key_exists($key, $original) || $original[$key] !== $value) {
                $changes[$key] = [
                    'old' => $original[$key] ?? null,
                    'new' => $value
                ];
            }
        }

        return $changes;
    }
}
