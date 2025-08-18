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
        // Drop existing timestamp columns
        Schema::table('markets', function (Blueprint $table) {
            $table->dropColumn(['created_at', 'updated_at']);
        });
        
        // Add SQL Server compatible timestamp columns
        Schema::table('markets', function (Blueprint $table) {
            $table->dateTime('created_at', 7)->nullable();
            $table->dateTime('updated_at', 7)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('markets', function (Blueprint $table) {
            $table->dropColumn(['created_at', 'updated_at']);
        });
        
        Schema::table('markets', function (Blueprint $table) {
            $table->timestamps();
        });
    }
};
