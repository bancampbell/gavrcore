<?php

use Illuminate\Support\Facades\Route;
use Modules\Shop\Infrastructure\Http\Controllers\ShopController;
use Modules\Shop\Infrastructure\Http\Controllers\CartController;
use Modules\Shop\Infrastructure\Http\Controllers\Admin\ProductController;

Route::prefix('shop')->group(function () {
    Route::get('/', [ShopController::class, 'index'])->name('shop.index');
    Route::get('/{slug}', [ShopController::class, 'show'])->name('shop.show');
    Route::get('/cart', [ShopController::class, 'cart'])->name('shop.cart');

    Route::post('/cart/add', [CartController::class, 'add'])->name('shop.cart.add');
    Route::post('/cart/remove', [CartController::class, 'remove'])->name('shop.cart.remove');
    Route::post('/cart/clear', [CartController::class, 'clear'])->name('shop.cart.clear');
});

Route::prefix('admin/shop')
    ->middleware(['auth', 'can:manage-shop'])
    ->group(function () {
        Route::get('/products', [ProductController::class, 'index'])->name('admin.shop.products');
        Route::post('/products', [ProductController::class, 'store'])->name('admin.shop.products.store');
        Route::put('/products/{id}', [ProductController::class, 'update'])->name('admin.shop.products.update');
        Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('admin.shop.products.destroy');
    });
