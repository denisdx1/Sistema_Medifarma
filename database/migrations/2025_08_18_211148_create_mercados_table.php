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
        Schema::create('mercados', function (Blueprint $table) {
            $table->id('id_mercado');
            $table->string('mercado', 100);
            $table->dateTime('fecha_update',7)->nullable();
            $table->dateTime('fecha_registro',7)->useCurrent();
            $table->boolean('estado')->default(true);
            $table->foreignId('id_usuario')->constrained('users', 'id');
            
            $table->index(['mercado', 'estado']);
            $table->index('id_usuario');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mercados');
    }
};
