<?php

use App\Modules\FormBuilder\Infrastructure\Http\Controllers\AdminFormCommandController;
use App\Modules\FormBuilder\Infrastructure\Http\Controllers\AdminFormQueryController;
use App\Modules\FormBuilder\Infrastructure\Http\Controllers\AdminSubmissionCommandController;
use App\Modules\FormBuilder\Infrastructure\Http\Controllers\AdminSubmissionQueryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'admin'])->group(function () {
    Route::prefix('admin/forms')->name('admin.forms.')->group(function () {
        Route::get('/', [AdminFormQueryController::class, 'index'])->name('index');
        Route::get('/list', [AdminFormQueryController::class, 'list'])->name('list');
        Route::get('/create', [AdminFormQueryController::class, 'create'])->name('create');
        Route::post('/', [AdminFormCommandController::class, 'store'])->name('store');
        Route::get('/{form}/edit', [AdminFormQueryController::class, 'edit'])->name('edit');
        Route::put('/{form}', [AdminFormCommandController::class, 'update'])->name('update');
        Route::put('/{form}/status', [AdminFormCommandController::class, 'updateStatus'])->name('status');
        Route::delete('/{form}', [AdminFormCommandController::class, 'destroy'])->name('destroy');
        Route::get('/{form}/builder', [AdminFormQueryController::class, 'builder'])->name('builder');
        Route::put('/{form}/fields', [AdminFormCommandController::class, 'updateFields'])->name('fields.update');
    });

    Route::prefix('admin/submissions')->name('admin.submissions.')->group(function () {
        Route::get('/', [AdminSubmissionQueryController::class, 'index'])->name('index');
        Route::get('/{id}', [AdminSubmissionQueryController::class, 'show'])->name('show');
        Route::delete('/{id}', [AdminSubmissionCommandController::class, 'destroy'])->name('destroy');
        Route::post('/mark-read', [AdminSubmissionCommandController::class, 'markAsReadBulk'])->name('mark-read');
        Route::post('/destroy-bulk', [AdminSubmissionCommandController::class, 'destroyBulk'])->name('destroy-bulk');
    });
});