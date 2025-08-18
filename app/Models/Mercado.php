<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mercado extends Model
{
    protected $table = 'mercados';
    protected $primaryKey = 'id_mercado';
    
    protected $fillable = [
        'mercado',
        'fecha_update',
        'fecha_registro',
        'estado',
        'id_usuario'
    ];

    protected $casts = [
        'fecha_update' => 'datetime',
        'fecha_registro' => 'datetime',
        'estado' => 'boolean'
    ];

    public $timestamps = false;

    /**
     * Relación con el usuario que creó el mercado
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }

    /**
     * Relación con los materiales asignados a este mercado
     */
    public function materiales(): HasMany
    {
        return $this->hasMany(Material::class, 'id_mercado', 'id_mercado');
    }

    /**
     * Scope para mercados activos
     */
    public function scopeActivos($query)
    {
        return $query->where('estado', true);
    }

    /**
     * Scope para buscar por nombre de mercado
     */
    public function scopeBuscarPorNombre($query, $nombre)
    {
        return $query->where('mercado', 'like', "%{$nombre}%");
    }
}
