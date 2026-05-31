<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\MaterialController;
use App\Http\Controllers\Admin\MediaController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Index');
});

Route::get('/admin/login', fn() => Inertia::render('Auth/Login'))->name('login');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/admin/dashboard', function () {
        return Inertia::render('Admin/Dashboard', [
            'user' => auth()->user(),
        ]);
    })->name('dashboard');

    Route::get('/admin/materials', [MaterialController::class, 'index'])->name('admin.materials.index');

    // Корзина и операции с ней
    Route::get('/admin/materials/trash', [MaterialController::class, 'trash'])->name('admin.materials.trash');
    Route::post('/admin/materials/bulk-trash', [MaterialController::class, 'bulkTrash'])->name('admin.materials.bulk-trash');
    Route::post('/admin/materials/restore', [MaterialController::class, 'restore'])->name('admin.materials.restore');
    Route::post('/admin/materials/force-delete', [MaterialController::class, 'forceDelete'])->name('admin.materials.force-delete');
    Route::post('/admin/materials/empty-trash', [MaterialController::class, 'emptyTrash'])->name('admin.materials.empty-trash');
    Route::post('/admin/materials/bulk-publish', [MaterialController::class, 'bulkPublish'])->name('admin.materials.bulk-publish');
    Route::post('/admin/materials/bulk-unpublish', [MaterialController::class, 'bulkUnpublish'])->name('admin.materials.bulk-unpublish');

    Route::resource('/admin/categories', CategoryController::class)->names('admin.categories');

    Route::get('/admin/materials/create', [App\Http\Controllers\Admin\MaterialController::class, 'create'])->name('admin.materials.create');
    Route::post('/admin/materials', [App\Http\Controllers\Admin\MaterialController::class, 'store'])->name('admin.materials.store');
    Route::get('/admin/materials/{material}/edit', [App\Http\Controllers\Admin\MaterialController::class, 'edit'])->name('admin.materials.edit');
    Route::put('/admin/materials/{material}', [App\Http\Controllers\Admin\MaterialController::class, 'update'])->name('admin.materials.update');
    Route::get('/admin/materials/list', [App\Http\Controllers\Admin\MaterialController::class, 'list']);


    Route::prefix('admin/media')->name('admin.media.')->group(function () {
        Route::get('/', [App\Http\Controllers\Admin\MediaController::class, 'index'])->name('index');
        Route::get('/contents', [App\Http\Controllers\Admin\MediaController::class, 'getContents']);
        Route::post('/folder', [App\Http\Controllers\Admin\MediaController::class, 'createFolder']);
        Route::get('/folders', [App\Http\Controllers\Admin\MediaController::class, 'getFolders']);

        Route::post('/rename', [App\Http\Controllers\Admin\MediaController::class, 'renameItem']);
        Route::delete('/item', [App\Http\Controllers\Admin\MediaController::class, 'deleteItem']);
        Route::post('/copy', [App\Http\Controllers\Admin\MediaController::class, 'copyItem']);
        Route::post('/upload', [App\Http\Controllers\Admin\MediaController::class, 'uploadFile']);


    });



});
