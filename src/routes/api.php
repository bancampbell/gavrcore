<?php

use App\Http\Controllers\Api\MaterialController;
use Illuminate\Support\Facades\Route;

// Роуты аутентификации API (/api/register, /api/login, /api/logout)
// подключаются из модуля UserManager

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/materials', [MaterialController::class, 'index']);
    Route::get('/materials/by-slug/{slug}', [MaterialController::class, 'getBySlug']);
});
