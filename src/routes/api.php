<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Api\MaterialController;
use App\Http\Controllers\Admin\GalleryController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/materials', [MaterialController::class, 'index']);
    Route::get('/materials/by-slug/{slug}', [MaterialController::class, 'getBySlug']);
});

// Публичный роут для получения галереи
Route::get('/galleries/{gallery}', [GalleryController::class, 'show']);
