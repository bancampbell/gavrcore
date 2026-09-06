<?php

use App\Modules\UserManager\Infrastructure\Http\Controllers\AdminAccessLevelCommandController;
use App\Modules\UserManager\Infrastructure\Http\Controllers\AdminAccessLevelQueryController;
use App\Modules\UserManager\Infrastructure\Http\Controllers\AdminGroupCommandController;
use App\Modules\UserManager\Infrastructure\Http\Controllers\AdminGroupQueryController;
use App\Modules\UserManager\Infrastructure\Http\Controllers\AdminUserCommandController;
use App\Modules\UserManager\Infrastructure\Http\Controllers\AdminUserQueryController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| UserManager — защищённые админские роуты
|--------------------------------------------------------------------------
|
| Файл подключается UserManagerServiceProvider с middleware-группой "web".
|
*/

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Users
    Route::get('/users', [AdminUserQueryController::class, 'index'])->name('users.index');
    Route::get('/users/create', [AdminUserQueryController::class, 'create'])->name('users.create');
    Route::get('/users/{id}/edit', [AdminUserQueryController::class, 'edit'])->name('users.edit');

    Route::post('/users', [AdminUserCommandController::class, 'store'])->name('users.store');
    Route::put('/users/{id}', [AdminUserCommandController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}', [AdminUserCommandController::class, 'destroy'])->name('users.destroy');

    Route::post('/users/bulk-block', [AdminUserCommandController::class, 'bulkBlock'])->name('users.bulk-block');
    Route::post('/users/bulk-unblock', [AdminUserCommandController::class, 'bulkUnblock'])->name('users.bulk-unblock');

    // Groups
    Route::get('/groups', [AdminGroupQueryController::class, 'index'])->name('groups.index');
    Route::get('/groups/create', [AdminGroupQueryController::class, 'create'])->name('groups.create');
    Route::get('/groups/{id}/edit', [AdminGroupQueryController::class, 'edit'])->name('groups.edit');

    Route::post('/groups', [AdminGroupCommandController::class, 'store'])->name('groups.store');
    Route::put('/groups/{id}', [AdminGroupCommandController::class, 'update'])->name('groups.update');
    Route::delete('/groups/{id}', [AdminGroupCommandController::class, 'destroy'])->name('groups.destroy');
    Route::post('/groups/{id}/status', [AdminGroupCommandController::class, 'updateStatus'])->name('groups.status');

    // Access Levels
    Route::get('/access-levels', [AdminAccessLevelQueryController::class, 'index'])->name('access-levels.index');
    Route::get('/access-levels/create', [AdminAccessLevelQueryController::class, 'create'])->name('access-levels.create');
    Route::get('/access-levels/{id}/edit', [AdminAccessLevelQueryController::class, 'edit'])->name('access-levels.edit');

    Route::post('/access-levels', [AdminAccessLevelCommandController::class, 'store'])->name('access-levels.store');
    Route::put('/access-levels/{id}', [AdminAccessLevelCommandController::class, 'update'])->name('access-levels.update');
    Route::delete('/access-levels/{id}', [AdminAccessLevelCommandController::class, 'destroy'])->name('access-levels.destroy');
    Route::post('/access-levels/ordering', [AdminAccessLevelCommandController::class, 'updateOrdering'])->name('access-levels.ordering');
    Route::patch('/access-levels/{id}/status', [AdminAccessLevelCommandController::class, 'updateStatus'])->name('access-levels.status');
});
