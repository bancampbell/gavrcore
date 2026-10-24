<?php

use App\Modules\UserDashboard\Infrastructure\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| UserDashboard — роуты личного кабинета пользователя
|--------------------------------------------------------------------------
|
| Файл подключается UserDashboardServiceProvider с middleware-группой "web".
| Все роуты требуют аутентификации.
|
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/profile', [DashboardController::class, 'profile'])->name('dashboard.profile');
    Route::get('/dashboard/settings', [DashboardController::class, 'settings'])->name('dashboard.settings');
    Route::get('/dashboard/tickets', [DashboardController::class, 'tickets'])->name('dashboard.tickets');
    Route::get('/dashboard/tickets/new', [DashboardController::class, 'ticketsCreate'])->name('dashboard.tickets.create');

    Route::put('/dashboard/profile', [DashboardController::class, 'updateProfile'])->name('dashboard.profile.update');
    Route::put('/dashboard/settings', [DashboardController::class, 'updateSettings'])->name('dashboard.settings.update');
});
