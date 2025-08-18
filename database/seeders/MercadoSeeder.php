<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Mercado;
use App\Models\User;

class MercadoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener un usuario administrador
        $admin = User::where('role', 'administrador')->first();
        $userId = $admin ? $admin->id : 1;
        
        $mercados = [
            'INSTITUCIONAL',
            'PRIVADO',
            'FARMACIAS',
            'HOSPITAL',
            'CLINICA',
            'RETAIL'
        ];
        
        foreach ($mercados as $nombreMercado) {
            Mercado::create([
                'mercado' => $nombreMercado,
                'fecha_registro' => now(),
                'estado' => true,
                'id_usuario' => $userId
            ]);
        }
    }
}
