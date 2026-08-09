<?php

use App\Modules\MaterialManager\Infrastructure\Http\Controllers\AdminMaterialCommandController;
use App\Modules\MaterialManager\Infrastructure\Http\Controllers\AdminMaterialQueryController;
use App\Modules\MaterialManager\Infrastructure\Http\Middleware\EnsureIdempotency;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::prefix('materials')
            ->name('materials.')
            ->group(function () {

                // Query routes
                Route::controller(AdminMaterialQueryController::class)->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('/trash', 'trash')->name('trash');
                    Route::get('/create', 'create')->name('create');
                    Route::get('/list', 'list')->name('list');
                    Route::get('/{id}/edit', 'edit')->name('edit');
                });

                // Command routes
                Route::middleware(['throttle:material-bulk', EnsureIdempotency::class])
                    ->controller(AdminMaterialCommandController::class)
                    ->group(function () {
                        Route::post('/', 'store')->name('store');
                        Route::put('/{id}', 'update')->name('update');
                        Route::post('/bulk-trash', 'bulkTrash')->name('bulk-trash');
                        Route::post('/restore', 'restore')->name('restore');
                        Route::post('/force-delete', 'forceDelete')->name('force-delete');
                        Route::post('/empty-trash', 'emptyTrash')->name('empty-trash');
                        Route::post('/bulk-publish', 'bulkPublish')->name('bulk-publish');
                        Route::post('/bulk-unpublish', 'bulkUnpublish')->name('bulk-unpublish');
                    });
            });
    });
