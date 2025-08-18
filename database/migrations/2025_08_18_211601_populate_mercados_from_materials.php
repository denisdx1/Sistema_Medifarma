<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Obtener un usuario administrador para asignar como creador de los mercados
        $adminUser = DB::table('users')->where('role', 'administrador')->first();
        $userId = $adminUser ? $adminUser->id : 1; // Fallback al usuario con ID 1
        
        // Obtener mercados únicos de la tabla materiales (excluyendo valores vacíos, null y "RESTO")
        $mercadosUnicos = DB::table('materiales')
            ->select('Mercado')
            ->whereNotNull('Mercado')
            ->where('Mercado', '!=', '')
            ->where('Mercado', '!=', 'null')
            ->where('Mercado', '!=', 'NULL')
            ->where('Mercado', '!=', 'RESTO')
            ->distinct()
            ->get();
        
        $fechaActual = now();
        
        // Insertar cada mercado único en la tabla mercados
        foreach ($mercadosUnicos as $mercado) {
            $nombreMercado = trim($mercado->Mercado);
            
            if (!empty($nombreMercado)) {
                DB::table('mercados')->insert([
                    'mercado' => $nombreMercado,
                    'fecha_registro' => $fechaActual,
                    'fecha_update' => null,
                    'estado' => true,
                    'id_usuario' => $userId
                ]);
            }
        }
        
        // Actualizar la tabla materiales para asignar los id_mercado correctos
        $mercadosCreados = DB::table('mercados')->get();
        
        foreach ($mercadosCreados as $mercadoCreado) {
            DB::table('materiales')
                ->where('Mercado', $mercadoCreado->mercado)
                ->update(['id_mercado' => $mercadoCreado->id_mercado]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Limpiar las relaciones en materiales
        DB::table('materiales')->update(['id_mercado' => null]);
        
        // Vaciar la tabla mercados
        DB::table('mercados')->truncate();
    }
};
