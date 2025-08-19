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
    Route::prefix('market-configuration')->name('market-configuration.')->group(function () {
        Route::get('/', [MarketConfigurationController::class, 'index'])->name('index');
        
        // Admin and Product Manager only routes
        Route::middleware('role:administrador,gerente_producto')->group(function () {
            Route::post('/create-market', [MarketConfigurationController::class, 'createMarket'])->name('create-market');
            Route::put('/markets/{id}', [MarketConfigurationController::class, 'updateMarket'])->name('update-market');
            Route::delete('/markets/{id}', [MarketConfigurationController::class, 'deleteMarket'])->name('delete-market');
            Route::post('/assign-material-to-market', [MarketConfigurationController::class, 'assignMaterialToMarket'])->name('assign-material-to-market');
            Route::post('/bulk-assign-markets', [MarketConfigurationController::class, 'bulkAssignMarkets'])->name('bulk-assign-markets');
            Route::get('/search-products-for-assignment', [MarketConfigurationController::class, 'searchProductsForAssignment'])->name('search-products-for-assignment');
            Route::post('/remove-market-from-material', [MarketConfigurationController::class, 'removeMarketFromMaterial'])->name('remove-market-from-material');
            Route::post('/edit-market-name', [MarketConfigurationController::class, 'editMarketName'])->name('edit-market-name');
        });
        
        // Read-only routes for all roles
        Route::get('/markets', [MarketConfigurationController::class, 'getMarkets'])->name('get-markets');
        Route::get('/all-markets', [MarketConfigurationController::class, 'getAllMarkets'])->name('get-all-markets');
        Route::get('/markets-paginated', [MarketConfigurationController::class, 'getMarketsPaginated'])->name('get-markets-paginated');
        Route::get('/products-by-market', [MarketConfigurationController::class, 'getProductsByMarket'])->name('products-by-market');
    });
    
    // Market Administration Routes - Only for Admin users
    Route::prefix('market-administration')->name('market-administration.')->middleware(['auth', 'role:administrador'])->group(function () {
        Route::get('/', [App\Http\Controllers\MarketAdministrationController::class, 'index'])->name('index');
        Route::post('/approve', [App\Http\Controllers\MarketAdministrationController::class, 'approve'])->name('approve');
        Route::post('/change-status', [App\Http\Controllers\MarketAdministrationController::class, 'changeStatus'])->name('change-status');
        Route::get('/pending-count', [App\Http\Controllers\MarketAdministrationController::class, 'getPendingCount'])->name('pending-count');
    });
    
    // API routes
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/markets', [MarketConfigurationController::class, 'getMarketsAPI'])->name('markets');
        Route::get('/product-details/{sku}', [MarketConfigurationController::class, 'getProductDetails'])->name('product-details');
    });
    
    // User Management Routes - Available for authenticated users
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [UserManagementController::class, 'index'])->name('index');
        Route::get('/create', [UserManagementController::class, 'create'])->name('create');
        Route::post('/', [UserManagementController::class, 'store'])->name('store');
        Route::get('/{user}/edit', [UserManagementController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserManagementController::class, 'update'])->name('update');
        Route::post('/{user}/toggle-status', [UserManagementController::class, 'toggleStatus'])->name('toggle-status');
        Route::get('/stats', [UserManagementController::class, 'getStats'])->name('stats');
    });
});
