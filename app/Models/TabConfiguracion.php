<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
// ...existing code...

class TabConfiguracion extends Model
{
    // ...existing code...

    protected $connection = 'sqlsrv';
    protected $table = 'ODS.TAB_CONFIGURACION';
    protected $primaryKey = 'idConfiguracion';
    public $timestamps = false;

    protected $fillable = [
        'idMercado',
        'codigo',
        'fuente',
        'fechaRegistro',
        'idSolicitud',
        'idEstado'
    ];

    protected $casts = [
        'fechaRegistro' => 'date',
        'idConfiguracion' => 'integer',
        'idMercado' => 'integer',
        'idSolicitud' => 'integer',
        'idEstado' => 'integer'
    ];

    /**
     * Relación con el mercado
     */
    public function mercado()
    {
        return $this->belongsTo(Mercado::class, 'idMercado', 'idMercado');
    }

    /**
     * Relación con el estado
     */
    public function estado()
    {
        return $this->belongsTo(\App\Models\Estado::class, 'idEstado', 'idEstado');
    }

    /**
     * Relación con la solicitud
     */
    public function solicitud()
    {
        return $this->belongsTo(\App\Models\Solicitud::class, 'idSolicitud', 'idSolicitud');
    }

    /**
     * Relación con productos IQVIA por código
     */
    public function productoIqvia()
    {
        return $this->hasOne(VmaeProductoIqvia::class, 'Codigo_Interno', 'codigo');
    }

    /**
     * Scope para filtrar por mercado
     */
    public function scopeByMercado($query, $idMercado)
    {
        return $query->where('idMercado', $idMercado);
    }

    /**
     * Scope para filtrar por fuente
     */
    public function scopeByFuente($query, $fuente)
    {
        return $query->where('fuente', $fuente);
    }

    /**
     * Scope para configuraciones activas
     */
    public function scopeActivas($query)
    {
        return $query->whereHas('estado', function($q) {
            $q->where('estado', 'ACTIVO');
        });
    }

    /**
     * Obtener todas las configuraciones con sus relaciones
     */
    public static function conRelaciones()
    {
        return static::with(['mercado', 'estado', 'solicitud', 'productoIqvia']);
    }

    /**
     * Obtener configuraciones por mercado específico
     */
    public static function porMercado($idMercado)
    {
        return static::where('idMercado', $idMercado)
                    ->with(['productoIqvia', 'estado'])
                    ->get();
    }
}
