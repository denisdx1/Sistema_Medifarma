<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 25 products without an SKU (the factory default)
        Product::factory()->count(25)->create();

        // Create 25 products and then generate a simple SKU for each
        Product::factory()->count(25)->create()->each(function ($product) {
            $brandCode = strtoupper(substr($product->brand->name, 0, 3));
            $marketCode = strtoupper(substr($product->market->name, 0, 3));
            $product->sku = $brandCode . '-' . $marketCode . '-' . $product->id;
            $product->save();
        });
    }
}