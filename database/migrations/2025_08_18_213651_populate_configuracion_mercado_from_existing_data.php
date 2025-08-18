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
        // Obtener un usuario administrador para asignar como solicitante
        $adminUser = DB::table('users')->where('role', 'administrador')->first();
        $userId = $adminUser ? $adminUser->id : 1;
        
        $fechaActual = now();
        
        // Obtener todas las combinaciones de materiales con mercados asignados
        // desde la tabla materiales que tengan id_mercado
        $materialesConMercado = DB::table('materiales as m')
            ->join('mercados as mer', 'm.id_mercado', '=', 'mer.id_mercado')
            ->select(
                'm.SKU as id_producto',
                'm.id_mercado',
                DB::raw($userId . ' as id_usuario'),
                DB::raw("'" . $fechaActual . "' as fecha_solicitud"),
                DB::raw("'" . $fechaActual . "' as fecha_aprobacion"),
                DB::raw('1 as estado'), // 1 = Aprobado (ya que están asignados)
                DB::raw("'Migración automática desde datos existentes' as aprobacion")
            )
            ->whereNotNull('m.id_mercado')
            ->get();
        
        // Insertar en configuracion_mercado
        foreach ($materialesConMercado as $material) {
            try {
                DB::table('configuracion_mercado')->insert([
                    'id_producto' => $material->id_producto,
                    'id_mercado' => $material->id_mercado,
                    'id_usuario' => $material->id_usuario,
                    'fecha_solicitud' => $material->fecha_solicitud,
                    'fecha_aprobacion' => $material->fecha_aprobacion,
                    'estado' => $material->estado,
                    'aprobacion' => $material->aprobacion
                ]);
            } catch (\Exception $e) {
                // Si hay duplicados, los ignoramos
                continue;
            }
        }
        
        // También crear registros pendientes para materiales que tienen mercado en el campo 'Mercado'
        // pero no tienen id_mercado asignado
        $materialesSinIdMercado = DB::table('materiales as m')
            ->leftJoin('mercados as mer', function($join) {
                $join->on('m.Mercado', '=', 'mer.mercado');
            })
            ->select(
                'm.SKU as id_producto',
                'mer.id_mercado',
                DB::raw($userId . ' as id_usuario'),
                DB::raw("'" . $fechaActual . "' as fecha_solicitud"),
                DB::raw('0 as estado'), // 0 = Pendiente
                DB::raw("'Solicitud automática basada en campo Mercado' as aprobacion")
            )
            ->whereNotNull('m.Mercado')
            ->where('m.Mercado', '!=', '')
            ->where('m.Mercado', '!=', 'null')
            ->where('m.Mercado', '!=', 'NULL')
            ->where('m.Mercado', '!=', 'RESTO')
            ->whereNull('m.id_mercado') // Solo los que no tienen id_mercado asignado
            ->whereNotNull('mer.id_mercado') // Solo si el mercado existe en la tabla mercados
            ->get();
        
        // Insertar solicitudes pendientes
        foreach ($materialesSinIdMercado as $material) {
            try {
                DB::table('configuracion_mercado')->insert([
                    'id_producto' => $material->id_producto,
                    'id_mercado' => $material->id_mercado,
                    'id_usuario' => $material->id_usuario,
                    'fecha_solicitud' => $material->fecha_solicitud,
                    'fecha_aprobacion' => null,
                    'estado' => $material->estado,
                    'aprobacion' => $material->aprobacion
                ]);
            } catch (\Exception $e) {
                // Si hay duplicados, los ignoramos
                continue;
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Limpiar la tabla configuracion_mercado
        DB::table('configuracion_mercado')->truncate();
    }
};
