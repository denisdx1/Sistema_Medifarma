<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    // Usar la nueva conexión y tabla
    protected $connection = 'sqlsrv';
    protected $table = 'ODS.TAB_USUARIO';
    protected $primaryKey = 'idUsuario';
    
    // No usar timestamps automáticos de Laravel
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'idRol',
        'usuario',
        'login',
        'password',
        'email',
        'fechaRegistro',
        'idEstado'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'idUsuario' => 'integer',
            'idRol' => 'integer',
            'usuario' => 'string',
            'login' => 'string',
            'email' => 'string',
            'fechaRegistro' => 'date',
            'idEstado' => 'integer',
            // No usar hashed para password ya que viene como varbinary(32)
        ];
    }    /**
     * Role constants - basado en datos reales de la BD
     * Según la exploración: tenemos usuarios con idRol = 1 y idRol = 2
     */
    public const ROLE_ADMIN = 1;
    public const ROLE_GERENTE_PRODUCTO = 2;

    /**
     * Role mapping for compatibility
     */
    private const ROLE_MAPPING = [
        1 => 'administrador',
        2 => 'gerente_producto'
    ];

    /**
     * Get all available roles - basado en datos reales
     */
    public static function getRoles(): array
    {
        return [
            1 => 'Administrador',
            2 => 'Gerente de Producto (GP)',
        ];
    }

    /**
     * Get all available franquicias from DTM_VENTAS.ODS.TAB_FRANQUICIA
     */
    public static function getFranquicias(): array
    {
        try {
            $franquicias = \DB::connection('sqlsrv')
                ->table('DTM_VENTAS.ODS.TAB_FRANQUICIA')
                ->select('idFranquicia', 'franquicia')
                ->orderBy('franquicia')
                ->get()
                ->pluck('franquicia', 'idFranquicia')
                ->toArray();
            
            return $franquicias;
        } catch (\Exception $e) {
            // En caso de error, retornar array vacío
            \Log::error('Error al obtener franquicias: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Validate if a franquicia exists in DTM_VENTAS.ODS.TAB_FRANQUICIA
     */
    public static function franquiciaExists(int $idFranquicia): bool
    {
        try {
            return \DB::connection('sqlsrv')
                ->table('DTM_VENTAS.ODS.TAB_FRANQUICIA')
                ->where('idFranquicia', $idFranquicia)
                ->exists();
        } catch (\Exception $e) {
            \Log::error('Error al validar franquicia: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Relación con las franquicias del usuario a través de la tabla pivot
     */
    public function usuarioFranquicias()
    {
        return $this->hasMany(UsuarioFranquicia::class, 'idUsuario', 'idUsuario');
    }

    /**
     * Relación con las franquicias activas del usuario
     */
    public function franquiciasActivas()
    {
        return $this->hasMany(UsuarioFranquicia::class, 'idUsuario', 'idUsuario')
                    ->where('idEstado', 1);
    }

    /**
     * Get franquicia name by ID
     */
    public function getFranquiciaNombre(): string
    {
        // Usar el modelo UsuarioFranquicia para obtener la primera franquicia asignada al usuario
        try {
            $usuarioFranquicia = $this->franquiciasActivas()->first();
            
            if (!$usuarioFranquicia) {
                return 'Sin franquicia';
            }
            
            return $usuarioFranquicia->franquicia_nombre;
        } catch (\Exception $e) {
            \Log::error('Error al obtener nombre de franquicia: ' . $e->getMessage());
            return 'Error al cargar franquicia';
        }
    }

    /**
     * Get all franquicias assigned to this user
     */
    public function getMisFranquicias(): array
    {
        try {
            return UsuarioFranquicia::getFranquiciasForUser($this->idUsuario);
        } catch (\Exception $e) {
            \Log::error('Error al obtener franquicias del usuario: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get franquicias IDs assigned to this user
     */
    public function getFranquiciasIds(): array
    {
        try {
            return UsuarioFranquicia::getFranquiciaIdsForUser($this->idUsuario);
        } catch (\Exception $e) {
            \Log::error('Error al obtener IDs de franquicias del usuario: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get formatted franquicias names for display
     */
    public function getFranquiciasNombres(): string
    {
        try {
            $franquicias = $this->getMisFranquicias();
            
            if (empty($franquicias)) {
                return 'Sin franquicias';
            }
            
            // Usar la clave correcta 'franquicia' en lugar de 'nombreFranquicia'
            $nombres = array_column($franquicias, 'franquicia');
            
            // Filtrar valores nulos o vacíos
            $nombres = array_filter($nombres, function($nombre) {
                return !empty($nombre) && $nombre !== 'N/A';
            });
            
            if (empty($nombres)) {
                return 'Sin franquicias válidas';
            }
            
            if (count($nombres) <= 2) {
                return implode(', ', $nombres);
            } else {
                return $nombres[0] . ', ' . $nombres[1] . ' +' . (count($nombres) - 2) . ' más';
            }
        } catch (\Exception $e) {
            \Log::error('Error al obtener nombres de franquicias del usuario: ' . $e->getMessage());
            return 'Error al cargar';
        }
    }

    /**
     * Check if user has access to a specific franquicia
     */
    public function hasFranquicia(int $idFranquicia): bool
    {
        try {
            return UsuarioFranquicia::userHasFranquicia($this->idUsuario, $idFranquicia);
        } catch (\Exception $e) {
            \Log::error('Error al verificar franquicia del usuario: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Asignar una franquicia al usuario
     */
    public function assignFranquicia(int $idFranquicia): bool
    {
        try {
            // Verificar si ya tiene la franquicia asignada
            if ($this->hasFranquicia($idFranquicia)) {
                return true; // Ya la tiene
            }
            
            UsuarioFranquicia::createRelation($this->idUsuario, $idFranquicia);
            return true;
        } catch (\Exception $e) {
            \Log::error('Error al asignar franquicia al usuario: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Remover una franquicia del usuario
     */
    public function removeFranquicia(int $idFranquicia): bool
    {
        try {
            $usuarioFranquicia = UsuarioFranquicia::active()
                ->forUser($this->idUsuario)
                ->forFranquicia($idFranquicia)
                ->first();
                
            if ($usuarioFranquicia) {
                return $usuarioFranquicia->deactivate();
            }
            
            return true; // No existía, así que ya está "removida"
        } catch (\Exception $e) {
            \Log::error('Error al remover franquicia del usuario: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get role display name
     */
    public function getRoleDisplayName(): string
    {
        return self::getRoles()[$this->idRol] ?? 'Sin Rol';
    }

    /**
     * Check if user has specific role (mantener compatibilidad)
     */
    public function hasRole(string $role): bool
    {
        // Convertir string role a ID
        $roleId = array_search($role, self::ROLE_MAPPING);
        return $this->idRol === $roleId;
    }

    /**
     * Verificar si el usuario es administrador
     */
    public function isAdmin()
    {
        return $this->idRol === self::ROLE_ADMIN;
    }

    /**
     * Verificar si el usuario es gerente de producto
     */
    public function isGerenteProducto()
    {
        return $this->idRol === self::ROLE_GERENTE_PRODUCTO;
    }

    /**
     * Verificar si el usuario es gerente de producto (alias para compatibilidad)
     */
    public function isProductManager()
    {
        return $this->isGerenteProducto();
    }

    /**
     * Relación con los mercados creados por este usuario
     * NOTA: Deshabilitado por ahora
     */
    public function mercados(): HasMany
    {
        // Retornar relación vacía 
        return $this->hasMany(Mercado::class, 'usuario_id_inexistente', 'idUsuario');
    }

    /**
     * Relación con las configuraciones de mercado solicitadas por este usuario
     * NOTA: Deshabilitado por ahora
     */
    public function configuracionesMercado(): HasMany
    {
        // Retornar relación vacía por ahora
        return $this->hasMany(ConfiguracionMercado::class, 'usuario_id_inexistente', 'idUsuario');
    }

    /**
     * Update last login timestamp (no aplicable en nueva estructura)
     */
    public function updateLastLogin(): void
    {
        // No hacer nada ya que no hay campo last_login_at en la nueva tabla
        // Se podría implementar con un log separado si es necesario
    }

    /**
     * Scope for active users
     */
    public function scopeActive($query)
    {
        return $query->where('idEstado', 1);
    }

    /**
     * Scope for users by role
     */
    public function scopeByRole($query, int $roleId)
    {
        return $query->where('idRol', $roleId);
    }

    /**
     * Scope for users by role string (compatibilidad)
     */
    public function scopeByRoleString($query, string $role)
    {
        $roleId = array_search($role, self::ROLE_MAPPING);
        if ($roleId !== false) {
            return $query->where('idRol', $roleId);
        }
        return $query->whereRaw('1 = 0'); // No results
    }

    // ========================================
    // ACCESSORS PARA COMPATIBILIDAD
    // ========================================

    /**
     * Accessor para 'name' (compatibilidad)
     */
    public function getNameAttribute()
    {
        return $this->usuario;
    }

    /**
     * Accessor para 'email' - usar email real o generar uno temporal
     */
    public function getEmailAttribute()
    {
        // Si hay un email real almacenado en la base de datos, usarlo
        if (!empty($this->attributes['email'])) {
            return $this->attributes['email'];
        }
        
        // Si no hay email, generar uno temporal basado en el login
        return $this->login . '@medifarma.com';
    }

    /**
     * Accessor para 'role' (compatibilidad)
     */
    public function getRoleAttribute()
    {
        return self::ROLE_MAPPING[$this->idRol] ?? 'sin_rol';
    }

    /**
     * Accessor para 'franquicia' (compatibilidad) - devuelve la primera franquicia
     */
    public function getFranquiciaAttribute()
    {
        return $this->getFranquiciaNombre();
    }

    /**
     * Accessor para 'is_active' (compatibilidad)
     */
    public function getIsActiveAttribute()
    {
        return $this->idEstado == 1;
    }

    /**
     * Accessor para 'id' (compatibilidad)
     */
    public function getIdAttribute()
    {
        return $this->idUsuario;
    }

    // ========================================
    // MÉTODOS PARA AUTENTICACIÓN
    // ========================================

    /**
     * Obtener el campo usado para el username en autenticación
     */
    public function getAuthIdentifierName()
    {
        return 'login'; // Usar 'login' en lugar de 'email'
    }

    /**
     * Obtener el identificador único del usuario
     */
    public function getAuthIdentifier()
    {
        return $this->login;
    }

    /**
     * Obtener la contraseña para autenticación
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * Verificar contraseña (método personalizado para varbinary con SHA2_256)
     */
    public function verificarPassword($password)
    {
        // Generar hash SHA2_256 de la contraseña en texto plano
        $passwordHasheada = hash('sha256', $password, true); // true para obtener binario
        
        // Comparar con la contraseña almacenada en la BD
        return $passwordHasheada === $this->password;
    }

    // ========================================
    // MÉTODOS ESTÁTICOS
    // ========================================

    /**
     * Buscar usuario por login
     */
    public static function findByLogin($login)
    {
        return self::where('login', $login)->where('idEstado', 1)->first();
    }

    /**
     * Obtener todos los usuarios activos
     */
    public static function getActiveUsers()
    {
        return self::active()->orderBy('usuario')->get();
    }

    /**
     * Crear nuevo usuario
     */
    public static function crearUsuario($datos)
    {
        $usuario = new self();
        $usuario->idRol = $datos['idRol'];
        $usuario->usuario = $datos['usuario'];
        $usuario->login = $datos['login'];
        $usuario->password = $datos['password']; // Debe venir ya hasheada en SHA2_256
        $usuario->franquicia = $datos['franquicia'] ?? null;
        $usuario->fechaRegistro = $datos['fechaRegistro'] ?? now()->format('Y-m-d');
        $usuario->idEstado = $datos['idEstado'] ?? 1;
        $usuario->save();
        
        return $usuario;
    }

    /**
     * Obtener información completa del usuario para debug
     */
    public function getDebugInfo()
    {
        return [
            'idUsuario' => $this->idUsuario,
            'idRol' => $this->idRol,
            'rol_nombre' => $this->getRoleDisplayName(),
            'usuario' => $this->usuario,
            'login' => $this->login,
            'franquicia' => $this->franquicia,
            'franquicias_ids' => $this->getFranquiciasIds(),
            'todas_franquicias' => $this->getMisFranquicias(),
            'fechaRegistro' => $this->fechaRegistro,
            'idEstado' => $this->idEstado,
            'is_active' => $this->is_active,
            'is_admin' => $this->isAdmin(),
            'is_gerente_producto' => $this->isGerenteProducto()
        ];
    }

    /**
     * Sincronizar franquicias del usuario (reemplaza todas las existentes)
     */
    public function syncFranquicias(array $franquiciasIds): bool
    {
        try {
            // Desactivar todas las franquicias actuales
            UsuarioFranquicia::forUser($this->idUsuario)
                ->update(['idEstado' => 0]);
            
            // Asignar las nuevas franquicias
            foreach ($franquiciasIds as $franquiciaId) {
                // Verificar si ya existe la relación
                $existing = UsuarioFranquicia::forUser($this->idUsuario)
                    ->forFranquicia($franquiciaId)
                    ->first();
                
                if ($existing) {
                    // Reactivar la existente
                    $existing->activate();
                } else {
                    // Crear nueva relación
                    UsuarioFranquicia::createRelation($this->idUsuario, $franquiciaId);
                }
            }
            
            return true;
        } catch (\Exception $e) {
            \Log::error('Error al sincronizar franquicias del usuario: ' . $e->getMessage());
            return false;
        }
    }
}
