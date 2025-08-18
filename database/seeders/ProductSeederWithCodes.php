<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\BusinessUnit;
use App\Models\Franchise;
use App\Models\Market;
use App\Models\Product;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ProductSeederWithCodes extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure related data exists
        $franchise = Franchise::firstOrCreate(['name' => 'Franquicia A']);
        $brand = Brand::firstOrCreate(['name' => 'Marca X']);
        $businessUnit = BusinessUnit::firstOrCreate(['name' => 'Unidad de Negocio 1']);
        $market = Market::firstOrCreate(['name' => 'Mercado Principal']);

        // Create products with SKU
        Product::factory()->count(10)->create([
            'franchise_id' => $franchise->id,
            'brand_id' => $brand->id,
            'business_unit_id' => $businessUnit->id,
            'market_id' => $market->id,
            'sku' => function () {
                return 'SKU-' . \Illuminate\Support\Str::random(8);
            },
        ]);

        // Create products without SKU
        Product::factory()->count(5)->create([
            'franchise_id' => $franchise->id,
            'brand_id' => $brand->id,
            'business_unit_id' => $businessUnit->id,
            'market_id' => $market->id,
            'sku' => null,
        ]);
    }
}
