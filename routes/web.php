<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Product\ProductController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get("/products", [ProductController::class, 'index'])->name('products.show');
Route::post("/products/create", [ProductController::class, 'create'])->name('products.create');
Route::get("/products/{idOrSlug}", [ProductController::class,'show'])->name('products.show');
Route::get("/products/{idOrSlug}/edit", [ProductController::class,'edit'])->name('products.edit');
Route::put("/products/{id}", [ProductController::class,'update'])->name('products.update');
Route::delete("/products/{id}", [ProductController::class,'destroy'])->name('products.destroy');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
