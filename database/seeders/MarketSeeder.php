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
            ['name' => 'Mercado 1', 'description' => 'Descripción del mercado 1'],
            ['name' => 'Mercado 2', 'description' => 'Descripción del mercado 2'],
        ]);
    }
}
