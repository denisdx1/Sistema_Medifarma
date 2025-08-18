<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Material extends Model
{
    protected $connection = 'sqlsrv'; // Conexión SQL Server
    protected $table = 'materiales'; // Nombre de tu tabla en SQL Server
    
    // Campos de la tabla
    protected $fillable = [
        'SKU',
        'Descripción_Presentación',
        'Código_ATC_4',
        'Descripción_ATC_4',
        'Código_FF_3',
        'Descripción_FF_3',
        'Molécula',
        'Marca_Genérico',
        'Ético_Popular',
        'Mercado',
        'LABORATORIO_C',
        'CORPORACION',
        'id_mercado'
    ];

    // Deshabilitar timestamps si la tabla no los tiene
    public $timestamps = false;

    // Si tu tabla no tiene una columna 'id' como primary key, especifica la correcta
    protected $primaryKey = 'SKU'; // Asumiendo que SKU es la clave primaria
    public $incrementing = false; // Si SKU no es auto-increment
    protected $keyType = 'string'; // Si SKU es string

    /**
     * Relación con el mercado asignado
     */
    public function mercadoAsignado(): BelongsTo
    {
        return $this->belongsTo(Mercado::class, 'id_mercado', 'id_mercado');
    }

    /**
     * Scope para buscar por términos globales
     */
    public function scopeGlobalSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('SKU', 'like', "%{$search}%")
              ->orWhere('Descripción_Presentación', 'like', "%{$search}%")
              ->orWhere('Molécula', 'like', "%{$search}%")
              ->orWhere('Marca_Genérico', 'like', "%{$search}%");
        });
    }

    /**
     * Scope para filtrar por marca genérico
     */
    public function scopeByMarcaGenerico($query, $marca)
    {
        return $query->where('Marca_Genérico', $marca);
    }

    /**
     * Scope para filtrar por ético popular
     */
    public function scopeByEticoPopular($query, $etico)
    {
        return $query->where('Ético_Popular', $etico);
    }

    /**
     * Scope para filtrar por mercado
     */
    public function scopeByMercado($query, $mercado)
    {
        return $query->where('Mercado', $mercado);
    }

    /**
     * Scope para materiales sin mercado asignado o con mercado RESTO
     */
    public function scopeSinMercadoAsignado($query)
    {
        return $query->where(function ($q) {
            $q->where('Mercado', 'RESTO')
              ->orWhereNull('Mercado')
              ->orWhere('Mercado', '')
              ->orWhere('Mercado', 'null')
              ->orWhere('Mercado', 'NULL');
        });
    }

    /**
     * Accessor para formatear el estado ético/popular
     */
    public function getEticoPopularFormattedAttribute()
    {
        return strtoupper($this->Ético_Popular) == 'ÉTICO' || strtoupper($this->Ético_Popular) == 'ETICO' ? 'Ético' : 'Popular';
    }

    /**
     * Accessor para formatear la marca genérico
     */
    public function getMarcaGenericoFormattedAttribute()
    {
        return strtoupper($this->Marca_Genérico) == 'MARCA' ? 'Marca' : 'Genérico';
    }

    /**
     * Accessor para formatear el mercado
     */
    public function getMercadoFormattedAttribute()
    {
        $mercado = trim($this->Mercado ?? '');
        
        if (empty($mercado) || strtolower($mercado) === 'null') {
            return 'Sin Asignar';
        }
        
        if (strtoupper($mercado) === 'RESTO') {
            return 'Resto';
        }
        
        // Capitalizar primera letra de cada palabra
        return ucwords(strtolower($mercado));
    }

    /**
     * Accessor para verificar si tiene mercado asignado
     */
    public function getHasMercadoAttribute()
    {
        $mercado = trim($this->Mercado ?? '');
        return !empty($mercado) && 
               strtolower($mercado) !== 'null' && 
               strtoupper($mercado) !== 'RESTO';
    }

    /**
     * Scope para filtrar por laboratorio
     */
    public function scopeByLaboratorio($query, $laboratorio)
    {
        return $query->where('LABORATORIO_C', $laboratorio);
    }

    /**
     * Scope para filtrar por corporación
     */
    public function scopeByCorporacion($query, $corporacion)
    {
        return $query->where('CORPORACION', $corporacion);
    }
}
