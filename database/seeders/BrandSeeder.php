<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('brands')->insert([
            ['name' => 'Marca 1', 'description' => 'Descripción de la marca 1'],
            ['name' => 'Marca 2', 'description' => 'Descripción de la marca 2'],
        ]);
    }
}
