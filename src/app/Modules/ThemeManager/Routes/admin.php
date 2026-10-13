<?php

use App\Modules\ThemeManager\Infrastructure\Http\Controllers\AdminThemeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| ThemeManager — защищённые админские роуты
|--------------------------------------------------------------------------
|
| Файл подключается ThemeManagerServiceProvider с middleware-группой "web".
|
*/

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Themes
    Route::get('/themes', [AdminThemeController::class, 'index'])->name('themes.index');
    Route::post('/themes', [AdminThemeController::class, 'update'])->name('themes.update');
});
