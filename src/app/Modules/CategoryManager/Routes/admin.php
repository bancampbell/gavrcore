<?php

use App\Modules\CategoryManager\Infrastructure\Http\Controllers\AdminCategoryCommandController;
use App\Modules\CategoryManager\Infrastructure\Http\Controllers\AdminCategoryQueryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('categories', [AdminCategoryQueryController::class, 'index'])->name('categories.index');
    Route::get('categories/all', [AdminCategoryQueryController::class, 'all'])->name('categories.all');
    Route::get('categories/tree', [AdminCategoryQueryController::class, 'tree'])->name('categories.tree');
    Route::get('categories/{id}', [AdminCategoryQueryController::class, 'show'])->name('categories.show');

    Route::post('categories', [AdminCategoryCommandController::class, 'store'])->name('categories.store');
    Route::post('categories/bulk-delete', [AdminCategoryCommandController::class, 'bulkDestroy'])->name('categories.bulk-delete');
    Route::put('categories/{id}', [AdminCategoryCommandController::class, 'update'])->name('categories.update');
    Route::delete('categories/{id}', [AdminCategoryCommandController::class, 'destroy'])->name('categories.destroy');
});
