<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ConfiguracionNotificacion extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'ODS.TAB_CONFIGURACION_NOTIFICACIONES';
    protected $primaryKey = 'idConfiguracion';
    
    public $timestamps = false;
    
    protected $fillable = [
        'correosDestinatarios',
        'activo'
    ];
    
    protected $casts = [
        'activo' => 'boolean',
        'fechaCreacion' => 'datetime',
        'fechaActualizacion' => 'datetime'
    ];
    
    /**
     * Obtener la configuración activa de notificaciones
     */
    public static function getConfiguracionActiva()
    {
        try {
            // Consultar directamente la tabla
            $result = DB::connection('sqlsrv')
                ->table('ODS.TAB_CONFIGURACION_NOTIFICACIONES')
                ->where('activo', 1)
                ->orderBy('idConfiguracion', 'desc')
                ->first();
            
            if ($result) {
                return $result;
            }
            
            // Si no hay configuración activa, buscar cualquier configuración
            $result = DB::connection('sqlsrv')
                ->table('ODS.TAB_CONFIGURACION_NOTIFICACIONES')
                ->orderBy('idConfiguracion', 'desc')
                ->first();
            
            return $result;
            
        } catch (\Exception $e) {
            \Log::error('Error obteniendo configuración de notificaciones', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    /**
     * Obtener TODOS los correos de TODAS las configuraciones
     */
    public static function getAllCorreosCombinados()
    {
        try {
            $todasConfiguraciones = self::getAllConfiguraciones();
            $todosCorreos = [];
            
            foreach ($todasConfiguraciones as $config) {
                if (!empty($config->correosDestinatarios)) {
                    $correos = explode(',', $config->correosDestinatarios);
                    foreach ($correos as $correo) {
                        $correo = trim($correo);
                        if (!empty($correo) && filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                            $todosCorreos[] = $correo;
                        }
                    }
                }
            }
            
            // Eliminar duplicados y retornar
            return array_unique($todosCorreos);
            
        } catch (\Exception $e) {
            \Log::error('Error obteniendo todos los correos combinados', [
                'error' => $e->getMessage()
            ]);
            return [];
        }
    }
    
    /**
     * Obtener configuración con TODOS los correos combinados
     */
    public static function getConfiguracionConTodosLosCorreos()
    {
        try {
            // Obtener la configuración activa
            $configuracion = self::getConfiguracionActiva();
            
            if ($configuracion) {
                // Combinar todos los correos de todas las configuraciones
                $todosCorreos = self::getAllCorreosCombinados();
                
                // Crear un objeto con todos los correos combinados
                $configuracionCombinada = clone $configuracion;
                $configuracionCombinada->correosDestinatarios = implode(', ', $todosCorreos);
                
                return $configuracionCombinada;
            }
            
            return null;
            
        } catch (\Exception $e) {
            \Log::error('Error obteniendo configuración con todos los correos', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
    
    /**
     * Obtener TODAS las configuraciones de notificaciones
     */
    public static function getAllConfiguraciones()
    {
        try {
            return DB::connection('sqlsrv')
                ->table('ODS.TAB_CONFIGURACION_NOTIFICACIONES')
                ->orderBy('idConfiguracion', 'desc')
                ->get();
        } catch (\Exception $e) {
            \Log::error('Error obteniendo todas las configuraciones de notificaciones', [
                'error' => $e->getMessage()
            ]);
            return collect();
        }
    }
    
    /**
     * Obtener configuraciones activas
     */
    public static function getConfiguracionesActivas()
    {
        try {
            return DB::connection('sqlsrv')
                ->table('ODS.TAB_CONFIGURACION_NOTIFICACIONES')
                ->where('activo', 1)
                ->orderBy('idConfiguracion', 'desc')
                ->get();
        } catch (\Exception $e) {
            \Log::error('Error obteniendo configuraciones activas', [
                'error' => $e->getMessage()
            ]);
            return collect();
        }
    }
    
    /**
     * Crear nueva configuración de notificaciones
     */
    public static function crearConfiguracion($correosDestinatarios)
    {
        try {
            $result = DB::connection('sqlsrv')
                ->select('EXEC ODS.SP_INSERT_CONFIGURACION_NOTIFICACION ?', [$correosDestinatarios]);
            
            if (!empty($result) && $result[0]->status === 'success') {
                return [
                    'success' => true,
                    'message' => $result[0]->message,
                    'idConfiguracion' => $result[0]->idConfiguracion
                ];
            }
            
            return [
                'success' => false,
                'message' => $result[0]->message ?? 'Error desconocido'
            ];
        } catch (\Exception $e) {
            \Log::error('Error creando configuración de notificaciones', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Error al crear la configuración: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Actualizar configuración existente
     */
    public static function actualizarConfiguracion($idConfiguracion, $correosDestinatarios)
    {
        try {
            $result = DB::connection('sqlsrv')
                ->select('EXEC ODS.SP_UPDATE_CONFIGURACION_NOTIFICACION ?, ?', [$idConfiguracion, $correosDestinatarios]);
            
            if (!empty($result) && $result[0]->status === 'success') {
                return [
                    'success' => true,
                    'message' => $result[0]->message
                ];
            }
            
            return [
                'success' => false,
                'message' => $result[0]->message ?? 'Error desconocido'
            ];
        } catch (\Exception $e) {
            \Log::error('Error actualizando configuración de notificaciones', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Error al actualizar la configuración: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Eliminar configuración
     */
    public static function eliminarConfiguracion($idConfiguracion)
    {
        try {
            $result = DB::connection('sqlsrv')
                ->select('EXEC ODS.SP_DELETE_CONFIGURACION_NOTIFICACION ?', [$idConfiguracion]);
            
            if (!empty($result) && $result[0]->status === 'success') {
                return [
                    'success' => true,
                    'message' => $result[0]->message
                ];
            }
            
            return [
                'success' => false,
                'message' => $result[0]->message ?? 'Error desconocido'
            ];
        } catch (\Exception $e) {
            \Log::error('Error eliminando configuración de notificaciones', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Error al eliminar la configuración: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Cambiar estado de la configuración
     */
    public static function cambiarEstado($idConfiguracion, $activo)
    {
        try {
            $result = DB::connection('sqlsrv')
                ->table('ODS.TAB_CONFIGURACION_NOTIFICACIONES')
                ->where('idConfiguracion', $idConfiguracion)
                ->update(['activo' => $activo]);
            
            if ($result > 0) {
                $estadoTexto = $activo ? 'activada' : 'desactivada';
                return [
                    'success' => true,
                    'message' => "Configuración {$estadoTexto} exitosamente"
                ];
            }
            
            return [
                'success' => false,
                'message' => 'No se pudo cambiar el estado de la configuración'
            ];
        } catch (\Exception $e) {
            \Log::error('Error cambiando estado de configuración de notificaciones', [
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Error al cambiar el estado: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Obtener correos configurados como array
     */
    public static function getCorreosConfigurados()
    {
        $configuracion = self::getConfiguracionActiva();
        
        if (!$configuracion || !$configuracion->activo) {
            return [];
        }
        
        $correos = $configuracion->correosDestinatarios;
        if (empty($correos)) {
            return [];
        }
        
        // Separar correos por comas y limpiar espacios
        $correosArray = array_map('trim', explode(',', $correos));
        
        // Filtrar correos vacíos
        return array_filter($correosArray, function($correo) {
            return !empty($correo) && filter_var($correo, FILTER_VALIDATE_EMAIL);
        });
    }
    
    /**
     * Método de debug para ver qué hay en la tabla
     */
    public static function debugTabla()
    {
        try {
            $todas = self::getAllConfiguraciones();
            $activas = self::getConfiguracionesActivas();
            $todosCorreosCombinados = self::getAllCorreosCombinados();
            
            return [
                'todas' => $todas,
                'activas' => $activas,
                'total_registros' => $todas->count(),
                'total_activas' => $activas->count(),
                'total_correos_unicos' => count($todosCorreosCombinados),
                'todos_correos_combinados' => $todosCorreosCombinados,
                'detalle_todas' => $todas->map(function($item) {
                    return [
                        'id' => $item->idConfiguracion,
                        'correos' => $item->correosDestinatarios,
                        'correos_array' => !empty($item->correosDestinatarios) ? 
                            array_map('trim', explode(',', $item->correosDestinatarios)) : [],
                        'activo' => $item->activo,
                        'fecha_creacion' => $item->fechaCreacion ?? 'N/A',
                        'fecha_actualizacion' => $item->fechaActualizacion ?? 'N/A'
                    ];
                })
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
                'todas' => [],
                'activas' => [],
                'total_registros' => 0,
                'total_activas' => 0,
                'total_correos_unicos' => 0,
                'todos_correos_combinados' => [],
                'detalle_todas' => []
            ];
        }
    }
}
