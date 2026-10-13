<?php

use App\Modules\SettingManager\Infrastructure\Http\Controllers\AdminSettingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| SettingManager — защищённые админские роуты
|--------------------------------------------------------------------------
|
| Файл подключается SettingManagerServiceProvider с middleware-группой "web".
|
*/

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Settings
    Route::get('/settings', [AdminSettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [AdminSettingController::class, 'update'])->name('settings.update');
});
