<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            MarketSeeder::class,
            BusinessUnitSeeder::class,
            BrandSeeder::class,
            FranchiseSeeder::class,
            ProductSeeder::class,
            ProductSeederWithCodes::class,
        ]);
    }
}
