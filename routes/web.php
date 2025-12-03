<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\OptimizationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Cart routes
Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::patch('/cart/items/{cartItem}/quantity', [CartController::class, 'updateQuantity'])->name('cart.update-quantity');
Route::patch('/cart/items/{cartItem}/toggle-selection', [CartController::class, 'toggleSelection'])->name('cart.toggle-selection');
Route::delete('/cart/items/{cartItem}', [CartController::class, 'remove'])->name('cart.remove');

// Optimization routes
Route::get('/api/optimize/cart', [OptimizationController::class, 'optimizeCart'])->name('optimize.cart');
Route::get('/api/optimize/cart-items/{cartItem}/suggestions', [OptimizationController::class, 'getSuggestions'])->name('optimize.suggestions');
Route::post('/api/optimize/cart-items/{cartItem}/apply', [OptimizationController::class, 'applyOptimization'])->name('optimize.apply');
