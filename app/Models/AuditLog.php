<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'ODS.TAB_AUDIT_LOG';
    protected $primaryKey = 'idAuditLog';
    
    // Deshabilitar timestamps automáticos porque usamos fecha_hora personalizada
    public $timestamps = false;
    
    protected $fillable = [
        'idUsuario',
        'accion',
        'stored_procedure',
        'descripcion',
        'ip_address',
        'datos_anteriores',
        'datos_nuevos',
        'resultado',
        'mensaje_error'
    ];

    protected $casts = [
        'fecha_hora' => 'datetime',
        'datos_anteriores' => 'array',
        'datos_nuevos' => 'array'
    ];

    /**
     * Relación con el modelo User
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'idUsuario', 'idUsuario');
    }

    /**
     * Crear un registro de auditoría
     */
    public static function crearLog(
        int $idUsuario,
        string $accion,
        string $descripcion,
        ?string $storedProcedure = null,
        ?array $datosAnteriores = null,
        ?array $datosNuevos = null,
        string $resultado = 'EXITOSO',
        ?string $mensajeError = null,
        ?string $ipAddress = null
    ): self {
        return static::create([
            'idUsuario' => $idUsuario,
            'accion' => $accion,
            'stored_procedure' => $storedProcedure,
            'descripcion' => $descripcion,
            'ip_address' => $ipAddress ?? request()->ip(),
            'datos_anteriores' => $datosAnteriores ? json_encode($datosAnteriores, JSON_UNESCAPED_UNICODE) : null,
            'datos_nuevos' => $datosNuevos ? json_encode($datosNuevos, JSON_UNESCAPED_UNICODE) : null,
            'resultado' => $resultado,
            'mensaje_error' => $mensajeError
        ]);
    }

    /**
     * Scope para filtrar por usuario
     */
    public function scopeByUsuario($query, int $idUsuario)
    {
        return $query->where('idUsuario', $idUsuario);
    }

    /**
     * Scope para filtrar por acción
     */
    public function scopeByAccion($query, string $accion)
    {
        return $query->where('accion', 'LIKE', "%{$accion}%");
    }

    /**
     * Scope para filtrar por fecha
     */
    public function scopeByFecha($query, string $fechaInicio, ?string $fechaFin = null)
    {
        $query->where('fecha_hora', '>=', $fechaInicio);
        
        if ($fechaFin) {
            $query->where('fecha_hora', '<=', $fechaFin);
        }
        
        return $query;
    }

    /**
     * Scope para filtrar por resultado
     */
    public function scopeByResultado($query, string $resultado)
    {
        return $query->where('resultado', $resultado);
    }

    /**
     * Obtener logs recientes
     */
    public function scopeRecientes($query, int $limit = 50)
    {
        return $query->orderBy('fecha_hora', 'desc')->limit($limit);
    }
}
