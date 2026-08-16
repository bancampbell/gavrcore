<?php

declare(strict_types=1);

use App\Modules\GalleryManager\Infrastructure\Http\Controllers\AdminGalleryCommandController;
use App\Modules\GalleryManager\Infrastructure\Http\Controllers\AdminGalleryQueryController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth:sanctum', 'admin'])->group(function () {
    Route::get('/admin/galleries', [AdminGalleryQueryController::class, 'index'])->name('admin.galleries.index');
    Route::get('/admin/galleries/list', [AdminGalleryQueryController::class, 'list'])->name('admin.galleries.list');
    Route::get('/admin/galleries/create', [AdminGalleryQueryController::class, 'create'])->name('admin.galleries.create');
    Route::get('/admin/galleries/{id}/edit', [AdminGalleryQueryController::class, 'edit'])->name('admin.galleries.edit');

    Route::post('/admin/galleries', [AdminGalleryCommandController::class, 'store'])->name('admin.galleries.store');
    Route::put('/admin/galleries/{id}', [AdminGalleryCommandController::class, 'update'])->name('admin.galleries.update');
    Route::delete('/admin/galleries/{id}', [AdminGalleryCommandController::class, 'destroy'])->name('admin.galleries.destroy');
    Route::post('/admin/galleries/{id}/publish', [AdminGalleryCommandController::class, 'publish'])->name('admin.galleries.publish');
    Route::post('/admin/galleries/{id}/unpublish', [AdminGalleryCommandController::class, 'unpublish'])->name('admin.galleries.unpublish');

    Route::post('/admin/galleries/{galleryId}/images', [AdminGalleryCommandController::class, 'uploadImage'])->name('admin.galleries.images.upload');
    Route::put('/admin/galleries/{galleryId}/images/{imageId}', [AdminGalleryCommandController::class, 'updateImage'])->name('admin.galleries.images.update');
    Route::delete('/admin/galleries/{galleryId}/images/{imageId}', [AdminGalleryCommandController::class, 'deleteImage'])->name('admin.galleries.images.delete');
});
