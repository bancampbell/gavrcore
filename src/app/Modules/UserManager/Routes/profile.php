<?php

use App\Modules\UserManager\Infrastructure\Http\Controllers\ProfileTwoFactorController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| UserManager — роуты профиля текущего пользователя (2FA)
|--------------------------------------------------------------------------
|
| Файл подключается UserManagerServiceProvider с middleware-группой "web".
| Общие для админки и кабинета: auth:sanctum принимает и сессию (web-гварда
| проверяется первой), и bearer-токен. Действия — только на себя.
|
*/

Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('/profile/2fa/enable', [ProfileTwoFactorController::class, 'enable'])->name('profile.2fa.enable');
    Route::post('/profile/2fa/confirm', [ProfileTwoFactorController::class, 'confirm'])->name('profile.2fa.confirm');
    Route::post('/profile/2fa/disable', [ProfileTwoFactorController::class, 'disable'])->name('profile.2fa.disable');
});
