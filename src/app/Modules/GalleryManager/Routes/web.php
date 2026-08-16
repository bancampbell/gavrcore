<?php

declare(strict_types=1);

use App\Modules\GalleryManager\Infrastructure\Http\Controllers\WebGalleryController;
use Illuminate\Support\Facades\Route;

Route::get('/galleries/{id}', [WebGalleryController::class, 'show']);
