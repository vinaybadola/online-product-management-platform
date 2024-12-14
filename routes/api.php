<?php

use App\Http\Controllers\Product\ProductController;
use Illuminate\Support\Facades\Route;

// Route::middleware('auth')->group(function () {
    Route::get("/products", [ProductController::class, 'index']); 
    Route::post('/products', [ProductController::class, 'store']); // Add new product
    Route::get('/products/{idOrSlug}', [ProductController::class, 'show']); // Show product details
    Route::put('/products/{id}', [ProductController::class, 'update']); // Update product
    Route::delete('/products/{id}', [ProductController::class, 'destroy']); // Delete product
//});
