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
        Schema::table('markets', function (Blueprint $table) {
            $table->string('code', 10)->nullable()->after('description');
            $table->boolean('is_active')->default(true)->after('code');
        });
        
        // Update existing records with generated codes
        DB::statement("
            UPDATE markets 
            SET code = 'MKT' + CAST(id AS VARCHAR(7))
            WHERE code IS NULL
        ");
        
        // Now make code unique and not nullable
        Schema::table('markets', function (Blueprint $table) {
            $table->string('code', 10)->nullable(false)->unique()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('markets', function (Blueprint $table) {
            $table->dropColumn(['code', 'is_active']);
        });
    }
};
