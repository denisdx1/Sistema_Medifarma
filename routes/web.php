<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('erp');
    }
    return redirect()->route('login');
});


use App\Http\Controllers\AuthController;
use App\Http\Controllers\ErpController;
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
    Route::get('/erp', [ErpController::class, 'index'])->name('erp');
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
    
    // Market Configuration Routes
    Route::prefix('market-configuration')->name('market-configuration.')->group(function () {
        Route::get('/', [MarketConfigurationController::class, 'index'])->name('index');
        Route::get('/available-markets', [MarketConfigurationController::class, 'showAvailableMarkets'])->name('available-markets');
        Route::post('/assign-market/{product}', [MarketConfigurationController::class, 'assignMarket'])->name('assign-market');
        Route::delete('/remove-market/{product}', [MarketConfigurationController::class, 'removeMarket'])->name('remove-market');
        Route::get('/create-market', [MarketConfigurationController::class, 'createMarket'])->name('create-market');
        Route::post('/create-market', [MarketConfigurationController::class, 'storeMarket'])->name('store-market');
        Route::get('/market/{market}', [MarketConfigurationController::class, 'showMarket'])->name('show-market');
        Route::post('/bulk-assign-market', [MarketConfigurationController::class, 'bulkAssignMarket'])->name('bulk-assign-market');
        Route::get('/export-unassigned', [MarketConfigurationController::class, 'exportUnassigned'])->name('export-unassigned');
    });
});
