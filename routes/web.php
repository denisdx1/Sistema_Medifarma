<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
   return redirect()->route('login');
});


use App\Http\Controllers\AuthController;
use App\Http\Controllers\ErpController;
use App\Http\Controllers\ProductController;

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
});
