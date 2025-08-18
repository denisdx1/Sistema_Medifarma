<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BusinessUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('business_units')->insert([
            ['name' => 'Unidad de Negocio 1', 'description' => 'Descripción de la unidad de negocio 1'],
            ['name' => 'Unidad de Negocio 2', 'description' => 'Descripción de la unidad de negocio 2'],
        ]);
    }
}
