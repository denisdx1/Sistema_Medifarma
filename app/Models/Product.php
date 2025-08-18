<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'sku',
        'brand_id',
        'franchise_id',
        'business_unit_id',
        'market_id',
    ];

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function franchise()
    {
        return $this->belongsTo(Franchise::class);
    }

    public function businessUnit()
    {
        return $this->belongsTo(BusinessUnit::class);
    }

    public function market()
    {
        return $this->belongsTo(Market::class);
    }
}