<?php

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

