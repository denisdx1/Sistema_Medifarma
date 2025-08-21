<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VmaeProductoIqvia extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'dbo.VMAE_PROD_IQVIA';
    
    // No tiene primary key definida, usamos la primera columna
    protected $primaryKey = 'Código_Presentación';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    // Todas las columnas de la vista
    protected $fillable = [
        'codigoPresentacion',
        'descripcionPresentacion',
        'fechaLanzamientoPresentacion',
        'sizePack',
        'stghVal',
        'stghMea',
        'voluMea',
        'voluVal',
        'formatoPresentacion',
        'codigoProducto',
        'marcaGenerico',
        'eticoPopular',
        'molecula',
        'codigoFF3',
        'descripcionFF3',
        'codigoFF2',
        'descripcionFF2',
        'codigoFF1',
        'descripcionFF1',
        'codigoATC4',
        'descripcionATC4',
        'codigoATC3',
        'descripcionATC3',
        'codigoATC2',
        'descripcionATC2',
        'codigoATC1',
        'descripcionATC1',
        'codigoLaboratorio',
        'descripcionLaboratorio',
        'codigoCorporacion',
        'descripcionCorporacion',
        'MERCADO',
        'origenCapital',
        'Fuente'
    ];

    protected $casts = [
        'Size_Pack' => 'integer',
        'Stgh_Val' => 'float',
        'Volu_Val' => 'float'
    ];

    /**
     * Relación con configuración de mercado por código interno
     */
    public function configuracion()
    {
        return $this->belongsTo(TabConfiguracion::class, 'Codigo_Interno', 'codigo');
    }

    /**
     * Scope para filtrar por mercado
     */
    public function scopeByMercado($query, $mercado)
    {
        return $query->where('MERCADO', $mercado);
    }

    /**
     * Scope para filtrar por laboratorio
     */
    public function scopeByLaboratorio($query, $laboratorio)
    {
        return $query->where('Laboratorio', $laboratorio);
    }

    /**
     * Scope para filtrar por fuente
     */
    public function scopeByFuente($query, $fuente)
    {
        return $query->where('Fuente', $fuente);
    }

    /**
     * Scope para productos éticos
     */
    public function scopeEticos($query)
    {
        return $query->where('Ético_Popular', 'ÉTICO');
    }

    /**
     * Scope para productos populares
     */
    public function scopePopulares($query)
    {
        return $query->where('Ético_Popular', 'POPULAR');
    }

    /**
     * Scope para filtrar por código ATC
     */
    public function scopeByCodigoATC($query, $nivel, $codigo)
    {
        $campo = "Código_ATC_{$nivel}";
        return $query->where($campo, $codigo);
    }

    /**
     * Obtener datos específicos para configuración de mercado
     */
    public static function getDatosConfiguracion($filtros = [])
    {
        $query = static::select([
            'Código_Presentación',
            'Descripción_Presentación',
            'Código_ATC_4',
            'Código_FF_3',
            'Descripción_Laboratorio',
            'Corporación',
            'Descripción_Corporación',
            'Ético_Popular',
            'Molécula',
            'MERCADO',
            'Codigo_Interno',
            'Laboratorio',
            'Fuente'
        ]);

        // Aplicar filtros
        if (isset($filtros['mercado'])) {
            $query->byMercado($filtros['mercado']);
        }

        if (isset($filtros['laboratorio'])) {
            $query->byLaboratorio($filtros['laboratorio']);
        }

        if (isset($filtros['fuente'])) {
            $query->byFuente($filtros['fuente']);
        }

        if (isset($filtros['etico_popular'])) {
            $query->where('Ético_Popular', $filtros['etico_popular']);
        }

        return $query;
    }

    /**
     * Obtener todos los datos para el backend
     */
    public static function getTodosDatos($filtros = [])
    {
        $query = static::query();

        // Aplicar filtros
        if (isset($filtros['mercado'])) {
            $query->byMercado($filtros['mercado']);
        }

        if (isset($filtros['laboratorio'])) {
            $query->byLaboratorio($filtros['laboratorio']);
        }

        if (isset($filtros['fuente'])) {
            $query->byFuente($filtros['fuente']);
        }

        return $query;
    }

    /**
     * Obtener laboratorios únicos
     */
    public static function getLaboratorios()
    {
        return static::select('Laboratorio')
                    ->distinct()
                    ->whereNotNull('Laboratorio')
                    ->orderBy('Laboratorio')
                    ->pluck('Laboratorio');
    }

    /**
     * Obtener mercados únicos
     */
    public static function getMercados()
    {
        return static::select('MERCADO')
                    ->distinct()
                    ->whereNotNull('MERCADO')
                    ->orderBy('MERCADO')
                    ->pluck('MERCADO');
    }

    /**
     * Obtener corporaciones únicas
     */
    public static function getCorporaciones()
    {
        return static::select('Corporación', 'Descripción_Corporación')
                    ->distinct()
                    ->whereNotNull('Corporación')
                    ->orderBy('Corporación')
                    ->get();
    }
}
