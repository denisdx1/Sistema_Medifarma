<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConfiguracionMercado extends Model
{
    protected $table = 'configuracion_mercado';
    
    protected $fillable = [
        'id_producto',
        'id_mercado',
        'id_usuario',
        'fecha_solicitud',
        'fecha_aprobacion',
        'estado',
        'aprobacion'
    ];

    protected $casts = [
        'fecha_solicitud' => 'datetime',
        'fecha_aprobacion' => 'datetime',
        'estado' => 'integer'
    ];

    public $timestamps = false;

    // Estados posibles
    public const ESTADO_PENDIENTE = 0;
    public const ESTADO_APROBADO = 1;
    public const ESTADO_RECHAZADO = 2;

    /**
     * Obtener array de estados disponibles
     */
    public static function getEstados(): array
    {
        return [
            self::ESTADO_PENDIENTE => 'Pendiente',
            self::ESTADO_APROBADO => 'Aprobado',
            self::ESTADO_RECHAZADO => 'Rechazado',
        ];
    }

    /**
     * Obtener nombre del estado actual
     */
    public function getEstadoNombreAttribute(): string
    {
        return self::getEstados()[$this->estado] ?? 'Desconocido';
    }

    /**
     * Relación con el material (producto)
     * NOTA: Retorna relación vacía ya que no existe tabla materiales
     */
    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class, 'id_producto_inexistente', 'SKU');
    }

    /**
     * Relación con el mercado
     */
    public function mercado(): BelongsTo
    {
        return $this->belongsTo(Mercado::class, 'id_mercado', 'id_mercado');
    }

    /**
     * Relación con el usuario que hizo la solicitud
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }

    /**
     * Scope para configuraciones pendientes
     */
    public function scopePendientes($query)
    {
        return $query->where('estado', self::ESTADO_PENDIENTE);
    }

    /**
     * Scope para configuraciones aprobadas
     */
    public function scopeAprobadas($query)
    {
        return $query->where('estado', self::ESTADO_APROBADO);
    }

    /**
     * Scope para configuraciones rechazadas
     */
    public function scopeRechazadas($query)
    {
        return $query->where('estado', self::ESTADO_RECHAZADO);
    }

    /**
     * Scope para filtrar por usuario
     */
    public function scopePorUsuario($query, $userId)
    {
        return $query->where('id_usuario', $userId);
    }

    /**
     * Scope para filtrar por mercado
     */
    public function scopePorMercado($query, $mercadoId)
    {
        return $query->where('id_mercado', $mercadoId);
    }

    /**
     * Marcar como aprobado
     */
    public function aprobar($comentario = null): bool
    {
        return $this->update([
            'estado' => self::ESTADO_APROBADO,
            'fecha_aprobacion' => now(),
            'aprobacion' => $comentario
        ]);
    }

    /**
     * Marcar como rechazado
     */
    public function rechazar($comentario = null): bool
    {
        return $this->update([
            'estado' => self::ESTADO_RECHAZADO,
            'fecha_aprobacion' => now(),
            'aprobacion' => $comentario
        ]);
    }
}
