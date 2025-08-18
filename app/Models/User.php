<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Carbon\Carbon;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'department',
        'is_active',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
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
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    /**
     * Role constants
     */
    public const ROLE_ADMIN = 'administrador';
    public const ROLE_PRODUCT_MANAGER = 'gerente_producto';
    public const ROLE_BUSINESS_INTELLIGENCE = 'business_intelligence';

    /**
     * Get all available roles
     */
    public static function getRoles(): array
    {
        return [
            self::ROLE_ADMIN => 'Administrador',
            self::ROLE_PRODUCT_MANAGER => 'Gerente de Producto (GP)',
            self::ROLE_BUSINESS_INTELLIGENCE => 'Business Intelligence (BI)',
        ];
    }

    /**
     * Get role display name
     */
    public function getRoleDisplayName(): string
    {
        return self::getRoles()[$this->role] ?? 'Sin Rol';
    }

    /**
     * Check if user has specific role
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole(self::ROLE_ADMIN);
    }

    /**
     * Check if user is product manager
     */
    public function isProductManager(): bool
    {
        return $this->hasRole(self::ROLE_PRODUCT_MANAGER);
    }

    /**
     * Check if user is business intelligence
     */
    public function isBusinessIntelligence(): bool
    {
        return $this->hasRole(self::ROLE_BUSINESS_INTELLIGENCE);
    }

    /**
     * Relación con los mercados creados por este usuario
     */
    public function mercados(): HasMany
    {
        return $this->hasMany(Mercado::class, 'id_usuario', 'id');
    }

    /**
     * Relación con las configuraciones de mercado solicitadas por este usuario
     */
    public function configuracionesMercado(): HasMany
    {
        return $this->hasMany(ConfiguracionMercado::class, 'id_usuario', 'id');
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }

    /**
     * Scope for active users
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for users by role
     */
    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }
}
