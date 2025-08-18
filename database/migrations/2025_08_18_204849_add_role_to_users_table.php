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
        Schema::table('users', function (Blueprint $table) {
            // Agregar campos relacionados con roles
            $table->enum('role', ['administrador', 'gerente_producto', 'business_intelligence'])
                  ->default('gerente_producto')
                  ->after('email');
            $table->string('department')->nullable()->after('role');
            $table->boolean('is_active')->default(true)->after('department');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
        });

        // Actualizar usuarios existentes con roles específicos
        DB::table('users')->update([
            'role' => 'administrador',
            'department' => 'TI',
            'is_active' => true
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'department', 'is_active', 'last_login_at']);
        });
    }
};
