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
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // User who performed the action
            $table->string('action'); // e.g., 'generated_sku', 'updated_product'
            $table->string('model_type')->nullable(); // e.g., 'App\Models\Product'
            $table->unsignedBigInteger('model_id')->nullable(); // ID of the affected model
            $table->text('old_value')->nullable(); // JSON or serialized old data
            $table->text('new_value')->nullable(); // JSON or serialized new data
            $table->ipAddress('ip_address')->nullable(); // IP address of the user
            $table->text('user_agent')->nullable(); // User agent string
            $table->dateTime('created_at',7)->nullable(); // Explicit datetime for SQL Server
            $table->dateTime('updated_at',7)->nullable(); // Explicit datetime for SQL Server
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs');
    }
};
