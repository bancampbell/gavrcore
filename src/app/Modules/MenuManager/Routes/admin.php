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
Route::get('/types/{id}', [AdminMenuTypeQueryController::class, 'show'])->name('admin.menu-types.show')->whereNumber('id');
Route::post('/types', [AdminMenuTypeCommandController::class, 'store'])->name('admin.menu-types.store');
Route::put('/types/{id}', [AdminMenuTypeCommandController::class, 'update'])->name('admin.menu-types.update')->whereNumber('id');
Route::delete('/types/{id}', [AdminMenuTypeCommandController::class, 'destroy'])->name('admin.menu-types.destroy')->whereNumber('id');
Route::post('/types/ordering/update', [AdminMenuTypeCommandController::class, 'updateOrdering'])->name('admin.menu-types.ordering');
Route::post('/types/{id}/status', [AdminMenuTypeCommandController::class, 'updateStatus'])->name('admin.menu-types.status')->whereNumber('id');

// ===== MENU ITEMS — STATIC FIRST, DYNAMIC AFTER =====
// All items (static paths MUST be before /items/{id})
Route::get('/items/all', [AdminMenuItemQueryController::class, 'getAllItemsPage'])->name('admin.menu-items.all-page');
Route::get('/items/all-data', [AdminMenuItemQueryController::class, 'getAllItems'])->name('admin.menu-items.all-data');

// Items by menu type
Route::get('/types/{menuTypeId}/items', [AdminMenuItemQueryController::class, 'index'])->name('admin.menu-items.index')->whereNumber('menuTypeId');
Route::get('/types/{menuTypeId}/items/tree', [AdminMenuItemQueryController::class, 'tree'])->name('admin.menu-items.tree')->whereNumber('menuTypeId');
Route::get('/types/{menuTypeId}/items/create', [AdminMenuItemQueryController::class, 'create'])->name('admin.menu-items.create')->whereNumber('menuTypeId');
Route::post('/types/{menuTypeId}/items', [AdminMenuItemCommandController::class, 'store'])->name('admin.menu-items.store')->whereNumber('menuTypeId');

// Single item operations (dynamic {id})
Route::get('/items/{id}', [AdminMenuItemQueryController::class, 'show'])->name('admin.menu-items.show')->whereNumber('id');
Route::get('/items/{id}/edit', [AdminMenuItemQueryController::class, 'edit'])->name('admin.menu-items.edit')->whereNumber('id');
Route::put('/items/{id}', [AdminMenuItemCommandController::class, 'update'])->name('admin.menu-items.update')->whereNumber('id');
Route::delete('/items/{id}', [AdminMenuItemCommandController::class, 'destroy'])->name('admin.menu-items.destroy')->whereNumber('id');
Route::post('/items/{id}/status', [AdminMenuItemCommandController::class, 'updateStatus'])->name('admin.menu-items.status')->whereNumber('id');
Route::post('/items/ordering/update', [AdminMenuItemCommandController::class, 'updateOrdering'])->name('admin.menu-items.ordering');
