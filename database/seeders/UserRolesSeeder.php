<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserRolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear usuario Administrador
        User::updateOrCreate(
            ['email' => 'admin@medifarma.com'],
            [
                'name' => 'Administrador Sistema',
                'password' => Hash::make('admin123'),
                'role' => 'administrador',
                'department' => 'TI',
                'is_active' => true,
                'email_verified_at' => now()
            ]
        );

        // Crear usuario Gerente de Producto
        User::updateOrCreate(
            ['email' => 'gp@medifarma.com'],
            [
                'name' => 'Gerente de Producto',
                'password' => Hash::make('gp123'),
                'role' => 'gerente_producto',
                'department' => 'Producto',
                'is_active' => true,
                'email_verified_at' => now()
            ]
        );

        // Crear usuario Business Intelligence
        User::updateOrCreate(
            ['email' => 'bi@medifarma.com'],
            [
                'name' => 'Business Intelligence',
                'password' => Hash::make('bi123'),
                'role' => 'business_intelligence',
                'department' => 'Inteligencia de Negocios',
                'is_active' => true,
                'email_verified_at' => now()
            ]
        );

        // Actualizar usuarios existentes sin rol
        User::whereNull('role')->update([
            'role' => 'gerente_producto',
            'department' => 'Producto',
            'is_active' => true
        ]);

        $this->command->info('Usuarios con roles creados exitosamente:');
        $this->command->info('- admin@medifarma.com (Administrador) - Password: admin123');
        $this->command->info('- gp@medifarma.com (Gerente Producto) - Password: gp123');
        $this->command->info('- bi@medifarma.com (Business Intelligence) - Password: bi123');
    }
}
