<?php

use App\Http\Controllers\Admin\GalleryController;
use App\Http\Controllers\Api\FormController;
use App\Http\Controllers\Api\MaterialController;
use App\Http\Controllers\Auth\Api\RegisterController;
use App\Http\Controllers\Auth\Admin\LoginController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [RegisterController::class, 'register'])
    ->middleware('throttle:register');
Route::post('/login', [LoginController::class, 'login'])
    ->middleware('throttle:login');
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/materials', [MaterialController::class, 'index']);
    Route::get('/materials/by-slug/{slug}', [MaterialController::class, 'getBySlug']);
});

// Публичный роут для получения галереи
Route::get('/galleries/{gallery}', [GalleryController::class, 'show']);

// ========================================
// API FORMS (публичные)
// ========================================
Route::prefix('forms')->group(function () {
    Route::get('/{id}', [FormController::class, 'show']);
    Route::post('/{id}/submit', [FormController::class, 'submit'])
        ->middleware('throttle:form-submit');
});
