<?php

use App\Modules\MaterialManager\Infrastructure\Http\Controllers\WebMaterialController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    Route::get('/', [WebMaterialController::class, 'index'])->name('home');
    Route::get('/search', [WebMaterialController::class, 'search'])->name('search');
    Route::get('/category/{slug}', [WebMaterialController::class, 'category'])->name('category.show');
    Route::get('/{slug}', [WebMaterialController::class, 'show'])
        ->name('material.show')
        ->where('slug', '^(?!login$|register$|password$|reset-password$|forgot-password$|verify-email$|email$|logout$|api$ |admin).*$');
});
