<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('configuracion_mercado', function (Blueprint $table) {
            $table->id();
            $table->string('id_producto'); // SKU del material
            $table->foreignId('id_mercado')->constrained('mercados', 'id_mercado');
            $table->foreignId('id_usuario')->constrained('users', 'id');
            $table->dateTime('fecha_solicitud')->useCurrent();
            $table->dateTime('fecha_aprobacion')->nullable();
            $table->tinyInteger('estado')->default(0); // 0: Pendiente, 1: Aprobado, 2: Rechazado
            $table->text('aprobacion')->nullable(); // Comentarios de aprobación
            
            // Índices para mejorar performance
            $table->index(['id_producto', 'id_mercado']);
            $table->index('estado');
            $table->index('fecha_solicitud');
            $table->index('id_usuario');
            
            // Constraint único para evitar duplicados de solicitud
            $table->unique(['id_producto', 'id_mercado'], 'unique_producto_mercado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracion_mercado');
    }
};
