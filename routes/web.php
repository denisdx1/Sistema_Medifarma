<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('market-configuration.index');
    }
    return redirect()->route('login');
});


use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\MarketConfigurationController;

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::get('/logout-now', [AuthController::class, 'logout'])->name('logout.now');

Route::middleware('auth')->group(function () {
    Route::post('/products/{product}/generate-sku', [ProductController::class, 'generateAndAssignSku'])->name('products.generateSku');
    
    // Test endpoint for API
    Route::get('/test-markets-api', function() {
        $markets = \App\Models\Market::where('is_active', true)->get();
        return response()->json([
            'success' => true,
            'markets' => $markets,
            'count' => $markets->count()
        ]);
    })->name('test.markets');
    
    // Market Configuration Routes - All roles can access with different permissions
    Route::prefix('market-configuration')->name('market-configuration.')->middleware('role:administrador,gerente_producto,business_intelligence')->group(function () {
        Route::get('/', [MarketConfigurationController::class, 'index'])->name('index');
        
        // Admin and Product Manager only routes
        Route::middleware('role:administrador,gerente_producto')->group(function () {
            Route::post('/create-market', [MarketConfigurationController::class, 'createMarket'])->name('create-market');
            Route::post('/remove-market-from-material', [MarketConfigurationController::class, 'removeMarketFromMaterial'])->name('remove-market-from-material');
            Route::post('/edit-market-name', [MarketConfigurationController::class, 'editMarketName'])->name('edit-market-name');
        });
        
        // Read-only routes for all roles
        Route::get('/markets', [MarketConfigurationController::class, 'getMarkets'])->name('get-markets');
        Route::get('/markets-paginated', [MarketConfigurationController::class, 'getMarketsPaginated'])->name('get-markets-paginated');
        Route::get('/products-by-market', [MarketConfigurationController::class, 'getProductsByMarket'])->name('products-by-market');
    });
    
    // API routes
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/markets', [MarketConfigurationController::class, 'getMarketsAPI'])->name('markets');
        Route::get('/product-details/{sku}', [MarketConfigurationController::class, 'getProductDetails'])->name('product-details');
    });
    
    // User Management Routes - Admin only
    Route::prefix('users')->name('users.')->middleware('role:administrador')->group(function () {
        Route::get('/', [UserManagementController::class, 'index'])->name('index');
        Route::get('/create', [UserManagementController::class, 'create'])->name('create');
        Route::post('/', [UserManagementController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [UserManagementController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserManagementController::class, 'update'])->name('update');
        Route::patch('/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('toggle-status');
        Route::get('/stats', [UserManagementController::class, 'getStats'])->name('stats');
    });
});
