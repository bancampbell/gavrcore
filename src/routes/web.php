<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ThemeController;
use App\Http\Controllers\Web\CookieConsentController;
use App\Http\Controllers\Web\SitemapController;
use App\Http\Controllers\Web\DashboardController as WebDashboardController;
use Illuminate\Support\Facades\Route;

// ===== COOKIE CONSENT =====
Route::post('/cookie-consent/accept', [CookieConsentController::class, 'accept'])->name('cookie.consent.accept');
Route::post('/cookie-consent/decline', [CookieConsentController::class, 'decline'])->name('cookie.consent.decline');

// Sitemap
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// ===== ЛИЧНЫЙ КАБИНЕТ ПОЛЬЗОВАТЕЛЯ =====
// (пока остаётся в монолите, будет перенесён отдельным модулем позже)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [WebDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/profile', [WebDashboardController::class, 'profile'])->name('dashboard.profile');
    Route::get('/dashboard/settings', [WebDashboardController::class, 'settings'])->name('dashboard.settings');
    Route::get('/dashboard/tickets', [WebDashboardController::class, 'tickets'])->name('dashboard.tickets');
    Route::get('/dashboard/tickets/new', [WebDashboardController::class, 'ticketsCreate'])->name('dashboard.tickets.create');

    Route::put('/dashboard/profile', [WebDashboardController::class, 'updateProfile'])->name('dashboard.profile.update');
    Route::put('/dashboard/settings', [WebDashboardController::class, 'updateSettings'])->name('dashboard.settings.update');
});

// ===== ЗАЩИЩЁННЫЕ АДМИНСКИЕ РОУТЫ =====
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Роуты категорий подключаются автоматически из модуля CategoryManager

    // Settings
    Route::get('/admin/settings', [SettingController::class, 'index'])->name('admin.settings.index');
    Route::post('/admin/settings', [SettingController::class, 'update'])->name('admin.settings.update');

    // Themes
    Route::get('/admin/themes', [ThemeController::class, 'index'])->name('admin.themes.index');
    Route::post('/admin/themes', [ThemeController::class, 'update'])->name('admin.themes.update');

    // ========================================
    // ПОЛЬЗОВАТЕЛИ, ГРУППЫ, УРОВНИ ДОСТУПА, АУТЕНТИФИКАЦИЯ,
    // МЕНЮ, ФОРМЫ, КАТЕГОРИИ И САБМИШЕНЫ — роуты подключаются
    // из модулей UserManager, MenuManager, FormBuilder и CategoryManager
    // ========================================
});
