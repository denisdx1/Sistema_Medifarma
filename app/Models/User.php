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
            'fechaRegistro' => 'date',
            'idEstado' => 'integer',
            // No usar hashed para password ya que viene como varbinary
        ];
    }

    /**
     * Role constants - mapeo con IDs de la nueva tabla
     */
    public const ROLE_ADMIN = 1;
    public const ROLE_PRODUCT_MANAGER = 2;
    public const ROLE_BUSINESS_INTELLIGENCE = 3;

    /**
     * Role mapping for compatibility
     */
    private const ROLE_MAPPING = [
        1 => 'administrador',
        2 => 'gerente_producto', 
        3 => 'business_intelligence'
    ];

    /**
     * Get all available roles
     */
    public static function getRoles(): array
    {
        return [
            1 => 'Administrador',
            2 => 'Gerente de Producto (GP)',
            3 => 'Business Intelligence (BI)',
        ];
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
    public function isProductManager()
    {
        return $this->idRol === self::ROLE_PRODUCT_MANAGER;
    }

    /**
     * Verificar si el usuario es de business intelligence
     */
    public function isBusinessIntelligence()
    {
        return $this->idRol === self::ROLE_BUSINESS_INTELLIGENCE;
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
     * Accessor para 'email' (compatibilidad)
     */
    public function getEmailAttribute()
    {
        return $this->login . '@medifarma.com'; // Simular email
    }

    /**
     * Accessor para 'role' (compatibilidad)
     */
    public function getRoleAttribute()
    {
        return self::ROLE_MAPPING[$this->idRol] ?? 'sin_rol';
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
        $usuario->password = $datos['password']; // Debe venir ya hasheada
        $usuario->fechaRegistro = $datos['fechaRegistro'] ?? now()->format('Y-m-d');
        $usuario->idEstado = $datos['idEstado'] ?? 1;
        $usuario->save();
        
        return $usuario;
    }
}
