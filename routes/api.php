<?php

use App\Http\Controllers\AuthTokenController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthTokenController::class, 'store'])->name('api.login');
Route::get('/categories', [CategoryController::class, 'index'])->name('api.categories.index');
Route::apiResource('products', ProductController::class)
    ->only(['index', 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthTokenController::class, 'destroy'])->name('api.logout');
    Route::apiResource('products', ProductController::class)
        ->only(['store', 'update', 'destroy']);
});
