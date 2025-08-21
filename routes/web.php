<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    // TEMPORAL: Saltarse autenticación - acceso directo a administración de mercados
    //return redirect()->route('market-administration.index');
    
    // CÓDIGO ORIGINAL COMENTADO - Descomentar cuando la autenticación esté lista
    if (Auth::check()) {
        return redirect()->route('market-management.index');
    }
    return redirect()->route('login');
});


use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\ProductController;

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
    
    // Market Management Routes - For Admin and Product Manager users
    // TEMPORAL: Middleware de autenticación comentado
    // Route::prefix('market-management')->name('market-management.')->group(function () {
    Route::prefix('market-management')->name('market-management.')->middleware(['auth', 'role:administrador,gerente_producto'])->group(function () {
        Route::get('/', [App\Http\Controllers\MarketManagementController::class, 'index'])->name('index');
        Route::post('/create', [App\Http\Controllers\MarketManagementController::class, 'createMarket'])->name('create');
        Route::put('/update', [App\Http\Controllers\MarketManagementController::class, 'updateMarket'])->name('update');
        Route::post('/toggle-status', [App\Http\Controllers\MarketManagementController::class, 'toggleStatus'])->name('toggle-status');
        Route::get('/search', [App\Http\Controllers\MarketManagementController::class, 'search'])->name('search');
    });
    
    // Temporary routes without middleware for testing
    Route::get('/market-management/market/{marketId}/products', [App\Http\Controllers\MarketManagementController::class, 'showProducts'])->name('market-management.products')->where('marketId', '[0-9]+');
    Route::get('/market-management/market/{marketId}/products/api', [App\Http\Controllers\MarketManagementController::class, 'getMarketProducts'])->name('market-management.products.api')->where('marketId', '[0-9]+');
    Route::get('/market-management/markets/api', [App\Http\Controllers\MarketManagementController::class, 'getMarketsApi'])->name('market-management.markets.api');
    Route::post('/market-management/products/remove', [App\Http\Controllers\MarketManagementController::class, 'removeProduct'])->name('market-management.products.remove');
    Route::post('/market-management/products/change-market', [App\Http\Controllers\MarketManagementController::class, 'changeProductMarket'])->name('market-management.products.change-market');
    Route::get('/market-management/resto-products', [App\Http\Controllers\MarketManagementController::class, 'getRestoProducts'])->name('market-management.resto-products');
    Route::get('/market-management/resto-filter-options', [App\Http\Controllers\MarketManagementController::class, 'getRestoFilterOptions'])->name('market-management.resto-filter-options');
    Route::post('/market-management/assign-products', [App\Http\Controllers\MarketManagementController::class, 'assignProducts'])->name('market-management.assign-products');
    
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
