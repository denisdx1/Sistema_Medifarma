<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    // TEMPORAL: Saltarse autenticación - acceso directo a administración de mercados
    //return redirect()->route('market-administration.index');
    
    // CÓDIGO ORIGINAL COMENTADO - Descomentar cuando la autenticación esté lista
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
    // Market Configuration Routes - Productos IQVIA
    Route::prefix('market-configuration')->name('market-configuration.')->group(function () {
        // Ruta principal para mostrar la página
        Route::get('/', [MarketConfigurationController::class, 'index'])->name('index');
        
        // Ruta para obtener productos con paginación y chunks
        Route::get('/productos', [MarketConfigurationController::class, 'getProductos'])->name('get-productos');
    });
    
    // Market Administration Routes - Only for Admin users
    // TEMPORAL: Middleware de autenticación comentado
    Route::prefix('market-administration')->name('market-administration.')->middleware(['auth', 'role:administrador'])->group(function () {
        Route::get('/', [App\Http\Controllers\MarketAdministrationController::class, 'index'])->name('index');
        Route::post('/approve', [App\Http\Controllers\MarketAdministrationController::class, 'approve'])->name('approve');
        Route::post('/deny', [App\Http\Controllers\MarketAdministrationController::class, 'deny'])->name('deny');
        Route::post('/change-status', [App\Http\Controllers\MarketAdministrationController::class, 'changeStatus'])->name('change-status');
        Route::get('/pending-count', [App\Http\Controllers\MarketAdministrationController::class, 'getPendingCount'])->name('pending-count');
        Route::get('/search', [App\Http\Controllers\MarketAdministrationController::class, 'search'])->name('search');
    });

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
    
    // Admin Productos Routes - Admin module for product approval workflow
    Route::prefix('admin-productos')->name('admin-productos.')->middleware(['auth', 'role:administrador'])->group(function () {
        Route::get('/', [App\Http\Controllers\AdminProductosController::class, 'index'])->name('index');
        Route::post('/aprobar', [App\Http\Controllers\AdminProductosController::class, 'aprobar'])->name('aprobar');
        Route::post('/denegar', [App\Http\Controllers\AdminProductosController::class, 'denegar'])->name('denegar');
        Route::get('/api/productos', [App\Http\Controllers\AdminProductosController::class, 'getProductosApi'])->name('productos.api');
    });

});
