<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'name' => 'gerencia1',
                'email' => 'admin@medifarma.com.pe',
                'password' => bcrypt('admin123456'),
            ],
            [
                'name' => 'Usuario 2',
                'email' => 'usuario2@example.com',
                'password' => bcrypt('password2'),
            ],
            [
                'name' => 'Usuario 3',
                'email' => 'usuario3@example.com',
                'password' => bcrypt('password3'),
            ],
        ]);
    }
}