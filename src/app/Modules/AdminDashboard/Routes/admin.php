<?php

use App\Modules\AdminDashboard\Infrastructure\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| AdminDashboard — защищённые админские роуты
|--------------------------------------------------------------------------
|
| Файл подключается AdminDashboardServiceProvider с middleware-группой "web".
|
*/

Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});
