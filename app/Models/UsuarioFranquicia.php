<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UsuarioFranquicia extends Model
{
    protected $connection = 'sqlsrv';
    protected $table = 'ODS.TAB_USUARIO_FRANQUICIA';
    protected $primaryKey = 'idUsuarioFranq';
    
    // No usar timestamps automáticos de Laravel
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'idUsuario',
        'idFranquicia',
        'fechaRegistro',
        'idEstado'
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'idUsuarioFranq' => 'integer',
            'idUsuario' => 'integer',
            'idFranquicia' => 'integer',
            'fechaRegistro' => 'date',
            'idEstado' => 'integer',
        ];
    }

    /**
     * Relación con el usuario
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'idUsuario', 'idUsuario');
    }

    /**
     * Relación con la franquicia (tabla externa)
     * Nota: No podemos usar un modelo Eloquent normal aquí porque la tabla está en DTM_VENTAS
     */
    public function franquicia()
    {
        return \DB::connection('sqlsrv')
            ->table('DTM_VENTAS.ODS.TAB_FRANQUICIA')
            ->where('idFranquicia', $this->idFranquicia)
            ->first();
    }

    /**
     * Obtener el nombre de la franquicia
     */
    public function getFranquiciaNombreAttribute(): string
    {
        try {
            $franquicia = \DB::connection('sqlsrv')
                ->table('DTM_VENTAS.ODS.TAB_FRANQUICIA')
                ->where('idFranquicia', $this->idFranquicia)
                ->value('franquicia');
            
            return $franquicia ?? 'Franquicia no encontrada';
        } catch (\Exception $e) {
            \Log::error('Error al obtener nombre de franquicia en UsuarioFranquicia', [
                'idFranquicia' => $this->idFranquicia,
                'error' => $e->getMessage()
            ]);
            return 'Error al cargar franquicia';
        }
    }

    /**
     * Scope for active usuario-franquicia relationships
     */
    public function scopeActive($query)
    {
        return $query->where('idEstado', 1);
    }

    /**
     * Scope for specific user
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('idUsuario', $userId);
    }

    /**
     * Scope for specific franquicia
     */
    public function scopeForFranquicia($query, int $franquiciaId)
    {
        return $query->where('idFranquicia', $franquiciaId);
    }

    /**
     * Verificar si la relación está activa
     */
    public function isActive(): bool
    {
        return $this->idEstado == 1;
    }

    /**
     * Activar la relación usuario-franquicia
     */
    public function activate(): bool
    {
        $this->idEstado = 1;
        return $this->save();
    }

    /**
     * Desactivar la relación usuario-franquicia
     */
    public function deactivate(): bool
    {
        $this->idEstado = 0;
        return $this->save();
    }

    /**
     * Crear nueva relación usuario-franquicia
     */
    public static function createRelation(int $userId, int $franquiciaId): self
    {
        return self::create([
            'idUsuario' => $userId,
            'idFranquicia' => $franquiciaId,
            'fechaRegistro' => now()->format('Y-m-d'),
            'idEstado' => 1
        ]);
    }

    /**
     * Obtener todas las franquicias activas de un usuario
     */
    public static function getFranquiciasForUser(int $userId): array
    {
        return self::active()
            ->forUser($userId)
            ->get()
            ->map(function ($usuarioFranquicia) {
                $franquicia = $usuarioFranquicia->franquicia();
                return [
                    'idFranquicia' => $usuarioFranquicia->idFranquicia,
                    'franquicia' => $franquicia->franquicia ?? 'N/A',
                    'fechaRegistro' => $usuarioFranquicia->fechaRegistro
                ];
            })
            ->toArray();
    }

    /**
     * Obtener solo los IDs de franquicias activas de un usuario
     */
    public static function getFranquiciaIdsForUser(int $userId): array
    {
        return self::active()
            ->forUser($userId)
            ->pluck('idFranquicia')
            ->toArray();
    }

    /**
     * Verificar si un usuario tiene acceso a una franquicia específica
     */
    public static function userHasFranquicia(int $userId, int $franquiciaId): bool
    {
        return self::active()
            ->forUser($userId)
            ->forFranquicia($franquiciaId)
            ->exists();
    }

    /**
     * Obtener todos los usuarios de una franquicia específica
     */
    public static function getUsersForFranquicia(int $franquiciaId): array
    {
        return self::active()
            ->forFranquicia($franquiciaId)
            ->with('usuario')
            ->get()
            ->pluck('usuario')
            ->toArray();
    }
}
