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
        // First drop the foreign key constraint
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['market_id']);
        });
        
        // Modify the column to allow NULL values
        DB::statement('ALTER TABLE products ALTER COLUMN market_id BIGINT NULL');
        
        // Re-add the foreign key constraint with nullable
        Schema::table('products', function (Blueprint $table) {
            $table->foreign('market_id')->references('id')->on('markets')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the foreign key constraint
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['market_id']);
        });
        
        // Set all NULL values to a default market (you may need to adjust this)
        DB::statement('UPDATE products SET market_id = 1 WHERE market_id IS NULL');
        
        // Make the column NOT NULL again
        DB::statement('ALTER TABLE products ALTER COLUMN market_id BIGINT NOT NULL');
        
        // Re-add the foreign key constraint
        Schema::table('products', function (Blueprint $table) {
            $table->foreign('market_id')->references('id')->on('markets');
        });
    }
};
};
