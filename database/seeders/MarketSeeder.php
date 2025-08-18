<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MarketSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('markets')->insert([
            [
                'name' => 'Mercado Nacional', 
                'description' => 'Mercado para productos nacionales',
                'code' => 'NAC',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Mercado Internacional', 
                'description' => 'Mercado para productos internacionales',
                'code' => 'INT',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Mercado Farmacéutico', 
                'description' => 'Mercado especializado en productos farmacéuticos',
                'code' => 'FARM',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Mercado Hospitalario', 
                'description' => 'Mercado para instituciones hospitalarias',
                'code' => 'HOSP',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'name' => 'Mercado Mayorista', 
                'description' => 'Mercado para ventas al por mayor',
                'code' => 'MAY',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }
}
