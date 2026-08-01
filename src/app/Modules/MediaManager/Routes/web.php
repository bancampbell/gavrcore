<?php

use Illuminate\Support\Facades\Route;
use Modules\MediaManager\Infrastructure\Http\Controllers\MediaController;

Route::middleware(['web', 'can:manage-media'])->prefix('admin/media')->group(function () {
    Route::get('/', [MediaController::class, 'index'])->name('admin.media.index');

    Route::get('/contents', [MediaController::class, 'getContents'])->name('admin.media.contents');
    Route::get('/contents/paginated', [MediaController::class, 'getPaginatedContents'])->name('admin.media.contents.paginated');
    Route::get('/folders', [MediaController::class, 'getFolders'])->name('admin.media.folders');

    Route::post('/folder', [MediaController::class, 'createFolder'])->name('admin.media.folder.create');
    Route::post('/rename', [MediaController::class, 'renameItem'])->name('admin.media.rename');
    Route::post('/copy', [MediaController::class, 'copyItem'])->name('admin.media.copy');
    Route::post('/upload', [MediaController::class, 'uploadFile'])->name('admin.media.upload');

    Route::delete('/item', [MediaController::class, 'deleteItem'])->name('admin.media.item.delete');
    Route::delete('/items', [MediaController::class, 'deleteItems'])->name('admin.media.items.delete');
});
