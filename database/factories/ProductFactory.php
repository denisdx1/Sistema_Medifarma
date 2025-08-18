<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\BusinessUnit;
use App\Models\Franchise;
use App\Models\Market;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Get existing foreign keys to ensure data integrity
        $brandIds = Brand::pluck('id')->toArray();
        $franchiseIds = Franchise::pluck('id')->toArray();
        $businessUnitIds = BusinessUnit::pluck('id')->toArray();
        $marketIds = Market::pluck('id')->toArray();

        return [
            'name' => ucwords($this->faker->words(3, true)),
            'description' => $this->faker->sentence(),
            'sku' => '', // Default to null, we will set it manually in the seeder for some products
            'brand_id' => $this->faker->randomElement($brandIds),
            'franchise_id' => $this->faker->randomElement($franchiseIds),
            'business_unit_id' => $this->faker->randomElement($businessUnitIds),
            'market_id' => $this->faker->randomElement($marketIds),
        ];
    }
}