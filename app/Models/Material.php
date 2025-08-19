<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Material extends Model
{
    // NOTA: Esta clase mantiene la estructura pero no consulta la BD
    // ya que la tabla 'materiales' no existe en la nueva base de datos
    
    protected $connection = 'sqlsrv'; 
    protected $table = 'materiales_inexistente'; // Tabla que no existe para evitar consultas
    
    // Campos de la tabla (mantenidos para compatibilidad)
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

    public $timestamps = false;
    protected $primaryKey = 'SKU';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Relación con el mercado asignado
     * NOTA: Retorna relación vacía ya que no existe tabla materiales
     */
    public function mercadoAsignado(): BelongsTo
    {
        return $this->belongsTo(Mercado::class, 'id_mercado_inexistente', 'idMercado');
    }

    /**
     * Relación con las configuraciones de mercado
     * NOTA: Retorna relación vacía ya que no existe tabla materiales
     */
    public function configuracionesMercado(): HasMany
    {
        return $this->hasMany(ConfiguracionMercado::class, 'id_producto_inexistente', 'SKU');
    }

    /**
     * Override de métodos de Query para devolver colecciones vacías
     * Esto evita que se ejecuten consultas a la tabla inexistente
     */
    public static function all($columns = ['*'])
    {
        return new Collection();
    }

    public static function get($columns = ['*'])
    {
        return new Collection();
    }

    public static function count()
    {
        return 0;
    }

    public static function find($id, $columns = ['*'])
    {
        return null;
    }

    public static function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        return new static;
    }

    public function scopeGlobalSearch($query, $search)
    {
        return $query;
    }

    public function scopeByMarcaGenerico($query, $marca)
    {
        return $query;
    }

    public function scopeByEticoPopular($query, $etico)
    {
        return $query;
    }

    public function scopeByMercado($query, $mercado)
    {
        return $query;
    }

    public function scopeSinMercadoAsignado($query)
    {
        return $query;
    }

    public function scopeByLaboratorio($query, $laboratorio)
    {
        return $query;
    }

    public function scopeByCorporacion($query, $corporacion)
    {
        return $query;
    }

    /**
     * Override del método paginate para evitar consultas
     */
    public static function paginate($perPage = 15, $columns = ['*'], $pageName = 'page', $page = null)
    {
        return new \Illuminate\Pagination\LengthAwarePaginator(
            new Collection(), // items vacíos
            0, // total
            $perPage,
            $page ?: 1,
            [
                'path' => request()->url(),
                'pageName' => $pageName,
            ]
        );
    }
}
