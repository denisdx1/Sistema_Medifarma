<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mercado extends Model
{
    // Usar la nueva conexión y vista
    protected $connection = 'sqlsrv';
    protected $table = 'DM.MERCADO';
    protected $primaryKey = 'idMercado';
    
    // Solo lectura desde la vista
    public $timestamps = false;
    
    protected $fillable = [
        'mercado'
    ];

    protected $casts = [
        'idMercado' => 'integer',
        'mercado' => 'string'
    ];

    // Prevenir modificaciones ya que es una vista
    public function save(array $options = [])
    {
        throw new \Exception('No se puede modificar la vista DM.MERCADO. Esta es solo de lectura.');
    }

    public function delete()
    {
        throw new \Exception('No se puede eliminar registros de la vista DM.MERCADO. Esta es solo de lectura.');
    }

    
    /**
     * Relación con las configuraciones de mercado
     * NOTA: Mantener compatibilidad con el sistema existente
     */
    public function configuracionesMercado(): HasMany
    {
        // Mantener compatibilidad con ConfiguracionMercado usando el nuevo ID
        return $this->hasMany(ConfiguracionMercado::class, 'id_mercado', 'idMercado');
    }

    /**
     * Scope para mercados activos - como es vista, todos están "activos"
     */
    public function scopeActivos($query)
    {
        return $query; // Todos los registros de la vista están activos
    }

    /**
     * Scope para buscar por nombre de mercado
     */
    public function scopeBuscarPorNombre($query, $nombre)
    {
        return $query->where('mercado', 'like', "%{$nombre}%");
    }

    /**
     * Método estático para obtener todos los mercados con paginación
     */
    public static function obtenerTodosPaginados($perPage = 15, $busqueda = null)
    {
        $query = self::query();
        
        if ($busqueda) {
            $query->buscarPorNombre($busqueda);
        }
        
        return $query->orderBy('mercado', 'asc')->paginate($perPage);
    }

    /**
     * Método para obtener todos los mercados como colección
     */
    public static function obtenerTodos()
    {
        return self::orderBy('mercado', 'asc')->get();
    }

    /**
     * Accessor para mantener compatibilidad con el campo 'estado'
     * Como es una vista, asumimos que todos están activos
     */
    public function getEstadoAttribute()
    {
        return true; // Todos los mercados de la vista están "activos"
    }

    /**
     * Accessor para mantener compatibilidad con 'id_mercado'
     */
    public function getIdMercadoAttribute()
    {
        return $this->attributes['idMercado'] ?? null;
    }

    /**
     * Accessor para fecha_registro (compatibilidad)
     */
    public function getFechaRegistroAttribute()
    {
        return now(); // Valor por defecto
    }

    /**
     * Relación con configuraciones del mercado
     */
    public function configuraciones()
    {
        return $this->hasMany(TabConfiguracion::class, 'idMercado', 'idMercado');
    }

    /**
     * Relación con productos IQVIA a través de configuraciones
     */
    public function productosIqvia()
    {
        return $this->hasManyThrough(
            VmaeProductoIqvia::class,
            TabConfiguracion::class,
            'idMercado', // Foreign key en tab_configuracion
            'Codigo_Interno', // Foreign key en vmae_prod_iqvia
            'idMercado', // Local key en mercado
            'codigo' // Local key en tab_configuracion
        );
    }

    /**
     * Obtener configuraciones activas del mercado
     */
    public function configuracionesActivas()
    {
        return $this->configuraciones()->activas();
    }
}
