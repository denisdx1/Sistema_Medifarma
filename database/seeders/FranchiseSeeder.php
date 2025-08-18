<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FranchiseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('franchises')->insert([
            ['name' => 'Franquicia 1', 'description' => 'Descripción de la franquicia 1'],
            ['name' => 'Franquicia 2', 'description' => 'Descripción de la franquicia 2'],
        ]);
    }
}
