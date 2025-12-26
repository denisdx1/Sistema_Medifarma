<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TabProducto extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'ODS.TAB_PRODUCTO';
    protected $primaryKey = 'idProducto';
    public $timestamps = false;

    protected $fillable = [
        'codigoPresentacion',
        'descripcionPresentacion',
        'fechaLanzamientoPresentacion',
        'sizePack',
        'Concentracion',
        'Volumen',
        'stghVal',
        'stghMea',
        'voluMea',
        'voluVal',
        'formatoPresentacion',
        'codigoProducto',
        'descripcionProducto',
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
        'origenCapital',
        'fuente'
    ];

    protected $casts = [
        'idProducto' => 'integer',
        'sizePack' => 'integer',
        'stghVal' => 'float',
        'voluVal' => 'float',
    ];

    /**
     * Scope para filtrar por código de producto
     */
    public function scopeByCodigoProducto($query, $codigo)
    {
        return $query->where('codigoProducto', $codigo);
    }

    /**
     * Scope para filtrar por código de presentación
     */
    public function scopeByCodigoPresentacion($query, $codigo)
    {
        return $query->where('codigoPresentacion', $codigo);
    }

    /**
     * Scope para filtrar por fuente
     */
    public function scopeByFuente($query, $fuente)
    {
        return $query->where('fuente', $fuente);
    }

    /**
     * Scope para filtrar por laboratorio
     */
    public function scopeByLaboratorio($query, $codigoLaboratorio)
    {
        return $query->where('codigoLaboratorio', $codigoLaboratorio);
    }

    /**
     * Scope para filtrar por corporación
     */
    public function scopeByCorporacion($query, $codigoCorporacion)
    {
        return $query->where('codigoCorporacion', $codigoCorporacion);
    }

    /**
     * Scope para filtrar por molécula
     */
    public function scopeByMolecula($query, $molecula)
    {
        return $query->where('molecula', 'LIKE', "%{$molecula}%");
    }

    /**
     * Scope para filtrar por marca/genérico
     */
    public function scopeByMarcaGenerico($query, $tipo)
    {
        return $query->where('marcaGenerico', $tipo);
    }

    /**
     * Scope para filtrar por código ATC (cualquier nivel)
     */
    public function scopeByATC($query, $codigo, $nivel = null)
    {
        if ($nivel) {
            return $query->where("codigoATC{$nivel}", $codigo);
        }
        
        // Buscar en todos los niveles ATC
        return $query->where(function($q) use ($codigo) {
            $q->where('codigoATC1', $codigo)
              ->orWhere('codigoATC2', $codigo)
              ->orWhere('codigoATC3', $codigo)
              ->orWhere('codigoATC4', $codigo);
        });
    }

    /**
     * Scope para filtrar por forma farmacéutica (cualquier nivel)
     */
    public function scopeByFF($query, $codigo, $nivel = null)
    {
        if ($nivel) {
            return $query->where("codigoFF{$nivel}", $codigo);
        }
        
        // Buscar en todos los niveles FF
        return $query->where(function($q) use ($codigo) {
            $q->where('codigoFF1', $codigo)
              ->orWhere('codigoFF2', $codigo)
              ->orWhere('codigoFF3', $codigo);
        });
    }

    /**
     * Buscar productos por descripción (producto o presentación)
     */
    public function scopeSearchByDescription($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('descripcionProducto', 'LIKE', "%{$search}%")
              ->orWhere('descripcionPresentacion', 'LIKE', "%{$search}%");
        });
    }

    /**
     * Obtener productos con información completa
     */
    public static function conInformacionCompleta()
    {
        return static::whereNotNull('descripcionProducto')
                    ->whereNotNull('codigoProducto');
    }

    /**
     * Obtener productos por laboratorio específico
     */
    public static function porLaboratorio($codigoLaboratorio)
    {
        return static::where('codigoLaboratorio', $codigoLaboratorio)
                    ->orderBy('descripcionProducto');
    }

    /**
     * Obtener productos por corporación específica
     */
    public static function porCorporacion($codigoCorporacion)
    {
        return static::where('codigoCorporacion', $codigoCorporacion)
                    ->orderBy('descripcionProducto');
    }

    /**
     * Accessor para obtener el nombre completo del producto
     */
    public function getNombreCompletoAttribute()
    {
        $nombre = $this->descripcionProducto ?: 'Producto sin nombre';
        if ($this->descripcionPresentacion) {
            $nombre .= ' - ' . $this->descripcionPresentacion;
        }
        return $nombre;
    }

    /**
     * Accessor para obtener información del laboratorio completa
     */
    public function getLaboratorioCompletoAttribute()
    {
        if ($this->descripcionLaboratorio) {
            return $this->descripcionLaboratorio . ' (' . $this->codigoLaboratorio . ')';
        }
        return $this->codigoLaboratorio;
    }

    /**
     * Accessor para obtener información de la corporación completa
     */
    public function getCorporacionCompletaAttribute()
    {
        if ($this->descripcionCorporacion) {
            return $this->descripcionCorporacion . ' (' . $this->codigoCorporacion . ')';
        }
        return $this->codigoCorporacion;
    }

    /**
     * Accessor para obtener información de volumen formateada
     */
    public function getVolumenFormateadoAttribute()
    {
        if ($this->voluVal && $this->voluMea) {
            return $this->voluVal . ' ' . $this->voluMea;
        }
        return null;
    }

    /**
     * Accessor para obtener información de concentración formateada
     */
    public function getConcentracionFormateadaAttribute()
    {
        if ($this->stghVal && $this->stghMea) {
            return $this->stghVal . ' ' . $this->stghMea;
        }
        return null;
    }
}
