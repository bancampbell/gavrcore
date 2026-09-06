<?php

use App\Modules\UserManager\Infrastructure\Http\Controllers\ApiAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| UserManager — API-роуты аутентификации
|--------------------------------------------------------------------------
|
| Файл подключается UserManagerServiceProvider с middleware-группой "api"
| и префиксом /api.
|
*/

Route::post('/register', [ApiAuthController::class, 'register'])
    ->middleware('throttle:register');
Route::post('/login', [ApiAuthController::class, 'login'])
    ->middleware('throttle:login');
Route::post('/logout', [ApiAuthController::class, 'logout'])
    ->middleware('auth:sanctum');
