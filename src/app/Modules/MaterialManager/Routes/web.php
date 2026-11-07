<?php

use App\Modules\MaterialManager\Infrastructure\Http\Controllers\WebMaterialController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->group(function () {
    Route::get('/', [WebMaterialController::class, 'index'])->name('home');
    Route::get('/search', [WebMaterialController::class, 'search'])->name('search');
    Route::get('/category/{slug}', [WebMaterialController::class, 'category'])->name('category.show');
    Route::get('/material/{slug}', [WebMaterialController::class, 'legacyShow'])
        ->name('material.show')
        ->where('slug', '^(?!login$|register$|password$|reset-password$|forgot-password$|verify-email$|email$|logout$|api$ |admin).*$');
    Route::get('/{category}/{slug}', [WebMaterialController::class, 'showInCategory'])
        ->name('material.category.show');
    Route::get('/{slug}', [WebMaterialController::class, 'showRoot'])
        ->name('material.root.show');
});
