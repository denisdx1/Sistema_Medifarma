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
        // Drop tables that are not needed
        Schema::dropIfExists('logs');
        Schema::dropIfExists('products');
        Schema::dropIfExists('markets');
        Schema::dropIfExists('business_units');
        Schema::dropIfExists('brands');
        Schema::dropIfExists('franchises');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the tables in reverse order (in case we need to rollback)
        
        // Franchises table
        Schema::create('franchises', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Brands table
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('franchise_id')->constrained('franchises');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Business Units table
        Schema::create('business_units', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('brand_id')->constrained('brands');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Markets table
        Schema::create('markets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Products table
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('business_unit_id')->constrained('business_units');
            $table->foreignId('market_id')->nullable()->constrained('markets');
            $table->decimal('price', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Logs table
        Schema::create('logs', function (Blueprint $table) {
            $table->id();
            $table->string('action');
            $table->string('model_type')->nullable();
            $table->unsignedBigInteger('model_id')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamp('created_at')->useCurrent();
        });
    }
};
