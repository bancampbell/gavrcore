<?php

use App\Modules\MenuManager\Infrastructure\Http\Controllers\AdminMenuItemCommandController;
use App\Modules\MenuManager\Infrastructure\Http\Controllers\AdminMenuItemQueryController;
use App\Modules\MenuManager\Infrastructure\Http\Controllers\AdminMenuTypeCommandController;
use App\Modules\MenuManager\Infrastructure\Http\Controllers\AdminMenuTypeQueryController;
use Illuminate\Support\Facades\Route;

// ===== ROOT: список типов меню =====
Route::get('/', [AdminMenuTypeQueryController::class, 'index'])->name('admin.menu.index');

// ===== MENU TYPES =====
Route::get('/types', [AdminMenuTypeQueryController::class, 'index'])->name('admin.menu-types.index');
Route::get('/types/{id}', [AdminMenuTypeQueryController::class, 'show'])->name('admin.menu-types.show');
Route::post('/types', [AdminMenuTypeCommandController::class, 'store'])->name('admin.menu-types.store');
Route::put('/types/{id}', [AdminMenuTypeCommandController::class, 'update'])->name('admin.menu-types.update');
Route::delete('/types/{id}', [AdminMenuTypeCommandController::class, 'destroy'])->name('admin.menu-types.destroy');
Route::post('/types/ordering/update', [AdminMenuTypeCommandController::class, 'updateOrdering'])->name('admin.menu-types.ordering');
Route::post('/types/{id}/status', [AdminMenuTypeCommandController::class, 'updateStatus'])->name('admin.menu-types.status');

// ===== MENU ITEMS — STATIC FIRST, DYNAMIC AFTER =====
// All items (static paths MUST be before /items/{id})
Route::get('/items/all', [AdminMenuItemQueryController::class, 'getAllItemsPage'])->name('admin.menu-items.all-page');
Route::get('/items/all-data', [AdminMenuItemQueryController::class, 'getAllItems'])->name('admin.menu-items.all-data');

// Items by menu type
Route::get('/types/{menuTypeId}/items', [AdminMenuItemQueryController::class, 'index'])->name('admin.menu-items.index');
Route::get('/types/{menuTypeId}/items/tree', [AdminMenuItemQueryController::class, 'tree'])->name('admin.menu-items.tree');
Route::get('/types/{menuTypeId}/items/create', [AdminMenuItemQueryController::class, 'create'])->name('admin.menu-items.create');
Route::post('/types/{menuTypeId}/items', [AdminMenuItemCommandController::class, 'store'])->name('admin.menu-items.store');

// Single item operations (dynamic {id})
Route::get('/items/{id}', [AdminMenuItemQueryController::class, 'show'])->name('admin.menu-items.show');
Route::get('/items/{id}/edit', [AdminMenuItemQueryController::class, 'edit'])->name('admin.menu-items.edit');
Route::put('/items/{id}', [AdminMenuItemCommandController::class, 'update'])->name('admin.menu-items.update');
Route::delete('/items/{id}', [AdminMenuItemCommandController::class, 'destroy'])->name('admin.menu-items.destroy');
Route::post('/items/{id}/status', [AdminMenuItemCommandController::class, 'updateStatus'])->name('admin.menu-items.status');
Route::post('/items/ordering/update', [AdminMenuItemCommandController::class, 'updateOrdering'])->name('admin.menu-items.ordering');
