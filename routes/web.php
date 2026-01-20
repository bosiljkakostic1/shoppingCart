<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\ShoppingCartController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    // API Routes
    Route::prefix('api')->group(function () {
        // Products
        Route::get('products', [ProductController::class, 'index'])->name('api.products.index');
        Route::get('products/{id}', [ProductController::class, 'show'])->name('api.products.show');
        Route::get('products/{id}/available-quantity', [ProductController::class, 'getAvailableQuantity'])->name('api.products.available-quantity');

        // Shopping Cart
        Route::get('cart', [ShoppingCartController::class, 'getActiveCart'])->name('api.cart.get');
        Route::post('cart/add', [ShoppingCartController::class, 'addProduct'])->name('api.cart.add');
        Route::post('cart/finish', [ShoppingCartController::class, 'finishOrder'])->name('api.cart.finish');
        Route::put('cart/products/{cartProductId}', [ShoppingCartController::class, 'updateQuantity'])->name('api.cart.update');
        Route::delete('cart/products/{cartProductId}', [ShoppingCartController::class, 'removeProduct'])->name('api.cart.remove');
    });
});

require __DIR__.'/settings.php';
