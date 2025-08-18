<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Market extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'code',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    /**
     * Get products assigned to this market
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Scope for active markets only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get formatted name with code
     */
    public function getFormattedNameAttribute(): string
    {
        return "{$this->name} ({$this->code})";
    }

    /**
     * Get products count for this market
     */
    public function getProductsCountAttribute(): int
    {
        return $this->products()->count();
    }
}
