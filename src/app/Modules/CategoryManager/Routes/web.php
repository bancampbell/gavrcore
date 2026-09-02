<?php

use App\Modules\CategoryManager\Infrastructure\Http\Controllers\WebCategoryController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('category/{slug}', [WebCategoryController::class, 'show'])->name('category.show');
});
