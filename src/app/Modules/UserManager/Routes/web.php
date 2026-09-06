<?php

use App\Modules\UserManager\Infrastructure\Http\Controllers\AdminAuthController;
use App\Modules\UserManager\Infrastructure\Http\Controllers\WebAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| UserManager — публичные web-роуты (аутентификация)
|--------------------------------------------------------------------------
|
| Файл подключается UserManagerServiceProvider с middleware-группой "web".
|
*/

// ===== ПОЛЬЗОВАТЕЛЬСКАЯ АУТЕНТИФИКАЦИЯ =====
Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
Route::post('/login', [WebAuthController::class, 'login'])->middleware('throttle:login');
Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');

// ===== РЕГИСТРАЦИЯ =====
Route::get('/register', [WebAuthController::class, 'showRegister'])->name('register');
Route::post('/register', [WebAuthController::class, 'register']);

// ===== АДМИНСКАЯ АУТЕНТИФИКАЦИЯ =====
Route::get('/admin/login', [AdminAuthController::class, 'create'])->name('admin.login');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->middleware('throttle:login');
Route::post('/admin/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
