<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class AutoAccessControlService
{
    /**
     * Configuración automática de acceso
     * - Se cierra el día 20 de cada mes
     * - Se habilita el último día del mes (30 o 31)
     */
    public function processAutoAccessControl(): array
    {
        $today = Carbon::now();
        $dayOfMonth = $today->day;
        $lastDayOfMonth = $today->copy()->endOfMonth()->day;
        
        $result = [
            'processed' => false,
            'action' => null,
            'message' => '',
            'config' => null
        ];

        try {
            // Verificar si es día 1 al 5 - SISTEMA ABIERTO
            if ($dayOfMonth >= 1 && $dayOfMonth <= 5) {
                $result = $this->openSystemForMonth($today);
            }
            // Cualquier otro día - SISTEMA CERRADO
            else {
                $result = $this->closeSystemForMonth($today);
            }

        } catch (\Exception $e) {
            Log::error('Error en AutoAccessControlService: ' . $e->getMessage());
            $result['message'] = 'Error al procesar control automático: ' . $e->getMessage();
        }

        return $result;
    }

    /**
     * Cerrar sistema (fuera del período del 1 al 5)
     */
    private function closeSystemForMonth(Carbon $date): array
    {
        $firstDayNextMonth = $date->copy()->day(1)->addMonth();
        
        // Configurar desde el día actual hasta el primer día del siguiente mes
        $config = [
            'fecha_inicio' => $date->copy()->setTime(0, 0, 0)->toISOString(),
            'fecha_fin' => $firstDayNextMonth->setTime(23, 59, 59)->toISOString(),
            'habilitado' => false,
            'configurado_por' => 'SISTEMA_AUTOMATICO',
            'configurado_en' => now()->toISOString(),
            'tipo_configuracion' => 'automatica',
            'descripcion' => 'Configuración automática - Sistema cerrado fuera del período del 1 al 5'
        ];

        $this->saveAccessConfig($config);

        return [
            'processed' => true,
            'action' => 'closed',
            'message' => "Sistema cerrado automáticamente desde {$date->format('d/m/Y')} hasta {$firstDayNextMonth->format('d/m/Y')}",
            'config' => $config
        ];
    }

    /**
     * Habilitar sistema el último día del mes
     */
    private function openSystemForMonth(Carbon $date): array
    {
        // Eliminar configuración de acceso (habilitar sistema)
        $this->removeAccessConfig();

        return [
            'processed' => true,
            'action' => 'opened',
            'message' => "Sistema habilitado automáticamente el {$date->format('d/m/Y')}",
            'config' => null
        ];
    }

    /**
     * Verificar estado actual sin cambios
     */
    private function checkCurrentStatus(Carbon $date): array
    {
        $configPath = storage_path('app/access_control.json');
        
        if (!File::exists($configPath)) {
            return [
                'processed' => false,
                'action' => 'check',
                'message' => 'Sistema habilitado - No hay restricciones activas',
                'config' => null
            ];
        }

        $config = json_decode(File::get($configPath), true);
        
        return [
            'processed' => false,
            'action' => 'check',
            'message' => 'Sistema con restricciones activas',
            'config' => $config
        ];
    }

    /**
     * Guardar configuración de acceso
     */
    private function saveAccessConfig(array $config): void
    {
        $configPath = storage_path('app/access_control.json');
        file_put_contents($configPath, json_encode($config, JSON_PRETTY_PRINT));
    }

    /**
     * Eliminar configuración de acceso
     */
    private function removeAccessConfig(): void
    {
        $configPath = storage_path('app/access_control.json');
        if (File::exists($configPath)) {
            File::delete($configPath);
        }
    }

    /**
     * Obtener información de configuración automática
     */
    public function getAutoConfigInfo(): array
    {
        $today = Carbon::now();
        $dayOfMonth = $today->day;
        $lastDayOfMonth = $today->copy()->endOfMonth()->day;
        
        $nextOpenDate = $today->copy()->day(1); // 1ro del próximo mes
        $nextCloseDate = $today->copy()->day(6); // 6to del mes actual o próximo
        
        // Si ya pasó el día 5 de este mes, calcular para el próximo mes
        if ($dayOfMonth > 5) {
            $nextOpenDate = $today->copy()->addMonth()->day(1); // 1ro del próximo mes
            $nextCloseDate = $today->copy()->addMonth()->day(6); // 6to del próximo mes
        }
        
        return [
            'current_day' => $dayOfMonth,
            'last_day_of_month' => $lastDayOfMonth,
            'is_close_day' => $dayOfMonth > 5,
            'is_open_day' => $dayOfMonth >= 1 && $dayOfMonth <= 5,
            'next_close_date' => $nextCloseDate->format('d/m/Y'),
            'next_open_date' => $nextOpenDate->format('d/m/Y'),
            'auto_config_enabled' => true,
            'description' => 'El sistema está abierto solo del día 1 al 5 de cada mes'
        ];
    }

    /**
     * Verificar si hay configuración automática activa
     */
    public function hasAutoConfig(): bool
    {
        $configPath = storage_path('app/access_control.json');
        
        if (!File::exists($configPath)) {
            return false;
        }

        $config = json_decode(File::get($configPath), true);
        
        return isset($config['tipo_configuracion']) && $config['tipo_configuracion'] === 'automatica';
    }
}
