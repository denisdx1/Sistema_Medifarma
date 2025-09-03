<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {

    if (Auth::check()) {
        return redirect()->route('market-management.index');
    }
    return redirect()->route('login');
});


use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\LogsController;
use App\Http\Controllers\ProductosController;

// Authentication routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
});

// Rutas de logout
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::get('/logout', [AuthController::class, 'logout'])->name('logout.get');


Route::middleware(['auth'])->group(function () {
    
    Route::prefix('market-management')->name('market-management.')->middleware(['auth', 'role:administrador,gerente_producto', 'password.change'])->group(function () {
        Route::get('/', [App\Http\Controllers\MarketManagementController::class, 'index'])->name('index');

        Route::put('/update', [App\Http\Controllers\MarketManagementController::class, 'updateMarket'])->name('update');
        Route::post('/toggle-status', [App\Http\Controllers\MarketManagementController::class, 'toggleStatus'])->name('toggle-status');
        Route::get('/search', [App\Http\Controllers\MarketManagementController::class, 'search'])->name('search');
        Route::get('/audit', [App\Http\Controllers\MarketManagementController::class, 'getMarketAudit'])->name('audit');
    });

    // Logs module - Solo administradores
    Route::prefix('logs')->name('logs.')->middleware(['auth', 'role:administrador', 'password.change'])->group(function () {
        Route::get('/', [App\Http\Controllers\LogsController::class, 'index'])->name('index');
        Route::get('/export', [App\Http\Controllers\LogsController::class, 'export'])->name('export');
        Route::get('/stats', [App\Http\Controllers\LogsController::class, 'getStats'])->name('stats');
    });
    
    // Temporary routes without middleware for testing
    Route::get('/market-management/markets/api', [App\Http\Controllers\MarketManagementController::class, 'getMarketsApi'])->name('market-management.markets.api');
    Route::get('/market-management/all-markets/api', [App\Http\Controllers\MarketManagementController::class, 'getAllMarketsApi'])->name('market-management.all-markets.api');
    Route::post('/market-management/products/remove', [App\Http\Controllers\MarketManagementController::class, 'removeProduct'])->name('market-management.products.remove');
    Route::post('/market-management/products/change-market', [App\Http\Controllers\MarketManagementController::class, 'changeProductMarket'])->name('market-management.products.change-market');
    Route::get('/market-management/resto-products', [App\Http\Controllers\MarketManagementController::class, 'getRestoProducts'])->name('market-management.resto-products');
    Route::get('/market-management/resto-filter-options', [App\Http\Controllers\MarketManagementController::class, 'getRestoFilterOptions'])->name('market-management.resto-filter-options');
    Route::post('/market-management/assign-products', [App\Http\Controllers\MarketManagementController::class, 'assignProducts'])->name('market-management.assign-products');
    Route::get('/market-management/productos-nuevos-sin-asignar', [App\Http\Controllers\MarketManagementController::class, 'getProductosNuevosYSinAsignar'])->name('market-management.productos-nuevos-sin-asignar');
    Route::get('/market-management/atc4-count', [App\Http\Controllers\MarketManagementController::class, 'getAtc4Count'])->name('market-management.atc4-count');
    Route::get('/market-management/atc4-list', [App\Http\Controllers\MarketManagementController::class, 'getAtc4List'])->name('market-management.atc4-list');
    
    // Productos Module - Available for all authenticated users
    Route::prefix('productos')->name('productos.')->group(function () {
        Route::get('/', [App\Http\Controllers\ProductosController::class, 'index'])->name('index');
        Route::get('/api', [App\Http\Controllers\ProductosController::class, 'getProductsApi'])->name('api');
        Route::get('/filter-options', [App\Http\Controllers\ProductosController::class, 'getFilterOptions'])->name('filter-options');
        Route::post('/remove', [App\Http\Controllers\ProductosController::class, 'removeProduct'])->name('remove');
        Route::post('/change-market', [App\Http\Controllers\ProductosController::class, 'changeProductMarket'])->name('change-market');
        Route::get('/markets/api', [App\Http\Controllers\ProductosController::class, 'getMarketsApi'])->name('markets.api');
        Route::post('/create-market', [App\Http\Controllers\ProductosController::class, 'createMarket'])->name('create-market');
        Route::post('/assign-products', [App\Http\Controllers\ProductosController::class, 'assignProducts'])->name('assign-products');
        Route::post('/bulk-remove-market', [App\Http\Controllers\ProductosController::class, 'bulkRemoveMarket'])->name('bulk-remove-market');
        Route::post('/bulk-change-market', [App\Http\Controllers\ProductosController::class, 'bulkChangeMarket'])->name('bulk-change-market');
    });
    
    // API routes for productos
    Route::prefix('api/productos')->name('api.productos.')->group(function () {
        Route::get('/filter-options', [App\Http\Controllers\ProductosController::class, 'getFilterOptions'])->name('filter-options');
    });
    
    // API routes for markets
    Route::prefix('api/markets')->name('api.markets.')->group(function () {
        Route::get('/search', [App\Http\Controllers\MarketManagementController::class, 'searchMarketsApi'])->name('search');
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

    // Nuevo Módulo de Usuarios Mejorado - Solo para administradores
    Route::prefix('usuarios')->name('usuarios.')->middleware(['auth', 'role:administrador', 'password.change'])->group(function () {
        Route::get('/', [UsuarioController::class, 'index'])->name('index');
        Route::post('/', [UsuarioController::class, 'store'])->name('store');
        Route::put('/{id}', [UsuarioController::class, 'update'])->name('update')->where('id', '[0-9]+');
        Route::delete('/{id}', [UsuarioController::class, 'destroy'])->name('destroy')->where('id', '[0-9]+');
        Route::post('/{id}/toggle-estado', [UsuarioController::class, 'toggleEstado'])->name('toggle-estado')->where('id', '[0-9]+');
        Route::get('/exportar', [UsuarioController::class, 'exportar'])->name('exportar');
        // API endpoints para modales
        Route::get('/{id}/datos', [UsuarioController::class, 'getDatos'])->name('get-datos')->where('id', '[0-9]+');
        
        // Rutas para configuración de notificaciones
        Route::get('/configuracion-notificaciones', [UsuarioController::class, 'getConfiguracionNotificaciones'])->name('configuracion-notificaciones');
        Route::post('/configuracion-notificaciones', [UsuarioController::class, 'storeConfiguracionNotificaciones'])->name('configuracion-notificaciones.store');
        Route::put('/configuracion-notificaciones/{id}', [UsuarioController::class, 'updateConfiguracionNotificaciones'])->name('configuracion-notificaciones.update');
        Route::delete('/configuracion-notificaciones/{id}', [UsuarioController::class, 'destroyConfiguracionNotificaciones'])->name('configuracion-notificaciones.destroy');
    });


    

    // Ruta para cambio de contraseña obligatorio - Para usuarios autenticados
    Route::middleware('auth')->group(function () {
        Route::get('/cambio-password-obligatorio', [UsuarioController::class, 'mostrarCambioPassword'])->name('usuarios.cambio-password');
        Route::post('/cambio-password-obligatorio', [UsuarioController::class, 'cambiarPasswordObligatorio'])->name('usuarios.cambio-password.submit');
    });

    // Rutas para cambio de contraseña obligatorio - Vista completa estilo login
    Route::middleware('guest')->group(function () {
        Route::get('/primer-cambio-password', [UsuarioController::class, 'mostrarCambioPassword'])->name('usuarios.primer-cambio-password');
        Route::post('/primer-cambio-password', [UsuarioController::class, 'cambiarPasswordObligatorio'])->name('usuarios.primer-cambio-password.submit');
    });

});

