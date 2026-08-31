<?php

use App\Http\Controllers\Admin\AccessLevelController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GroupController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ThemeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\Admin\LoginController as AdminLoginController;
use App\Http\Controllers\Auth\User\LoginController as UserLoginController;
use App\Http\Controllers\Auth\User\RegisterController;
use App\Http\Controllers\Web\CookieConsentController;
use App\Http\Controllers\Web\SitemapController;
use App\Http\Controllers\Web\DashboardController as WebDashboardController;
use Illuminate\Support\Facades\Route;

// ===== COOKIE CONSENT =====
Route::post('/cookie-consent/accept', [CookieConsentController::class, 'accept'])->name('cookie.consent.accept');
Route::post('/cookie-consent/decline', [CookieConsentController::class, 'decline'])->name('cookie.consent.decline');

// Sitemap
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

// ===== ПОЛЬЗОВАТЕЛЬСКАЯ АУТЕНТИФИКАЦИЯ =====
Route::get('/login', [UserLoginController::class, 'create'])->name('login');
Route::post('/login', [UserLoginController::class, 'store']);
Route::post('/logout', [UserLoginController::class, 'logout'])->name('logout');

// ===== РЕГИСТРАЦИЯ =====
Route::get('/register', [RegisterController::class, 'create'])->name('register');
Route::post('/register', [RegisterController::class, 'store']);

// ===== ЛИЧНЫЙ КАБИНЕТ ПОЛЬЗОВАТЕЛЯ =====
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [WebDashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/profile', [WebDashboardController::class, 'profile'])->name('dashboard.profile');
    Route::get('/dashboard/settings', [WebDashboardController::class, 'settings'])->name('dashboard.settings');
    Route::get('/dashboard/tickets', [WebDashboardController::class, 'tickets'])->name('dashboard.tickets');
    Route::get('/dashboard/tickets/new', [WebDashboardController::class, 'ticketsCreate'])->name('dashboard.tickets.create');

    Route::put('/dashboard/profile', [WebDashboardController::class, 'updateProfile'])->name('dashboard.profile.update');
    Route::put('/dashboard/settings', [WebDashboardController::class, 'updateSettings'])->name('dashboard.settings.update');
});

// ===== АДМИНСКАЯ АУТЕНТИФИКАЦИЯ =====
Route::get('/admin/login', [AdminLoginController::class, 'create'])->name('admin.login');
Route::post('/admin/login', [AdminLoginController::class, 'login'])->middleware('throttle:login');
Route::post('/admin/logout', [AdminLoginController::class, 'logout'])->name('admin.logout');

// ===== ЗАЩИЩЁННЫЕ АДМИНСКИЕ РОУТЫ =====
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    Route::resource('/admin/categories', CategoryController::class)->names('admin.categories');

    // User, Group, Permission, AccessLevel Manager
    Route::prefix('admin')->name('admin.')->group(function () {
        // Users
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');

        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

        Route::post('/users/bulk-block', [UserController::class, 'bulkBlock'])->name('users.bulk-block');
        Route::post('/users/bulk-unblock', [UserController::class, 'bulkUnblock'])->name('users.bulk-unblock');

        // Groups
        Route::get('/groups', [GroupController::class, 'index'])->name('groups.index');
        Route::get('/groups/create', [GroupController::class, 'create'])->name('groups.create');
        Route::get('/groups/{id}/edit', [GroupController::class, 'edit'])->name('groups.edit');

        Route::post('/groups', [GroupController::class, 'store'])->name('groups.store');
        Route::put('/groups/{id}', [GroupController::class, 'update'])->name('groups.update');
        Route::delete('/groups/{id}', [GroupController::class, 'destroy'])->name('groups.destroy');
        Route::post('/groups/{id}/status', [GroupController::class, 'updateStatus'])->name('groups.status');

        // Access Levels
        Route::get('/access-levels', [AccessLevelController::class, 'index'])->name('access-levels.index');
        Route::get('/access-levels/create', [AccessLevelController::class, 'create'])->name('access-levels.create');
        Route::post('/access-levels', [AccessLevelController::class, 'store'])->name('access-levels.store');
        Route::get('/access-levels/{id}/edit', [AccessLevelController::class, 'edit'])->name('access-levels.edit');
        Route::put('/access-levels/{id}', [AccessLevelController::class, 'update'])->name('access-levels.update');
        Route::delete('/access-levels/{id}', [AccessLevelController::class, 'destroy'])->name('access-levels.destroy');
        Route::post('/access-levels/ordering', [AccessLevelController::class, 'updateOrdering'])->name('access-levels.ordering');
        Route::patch('/access-levels/{id}/status', [AccessLevelController::class, 'updateStatus'])->name('access-levels.status');
    });

    // Settings
    Route::get('/admin/settings', [SettingController::class, 'index'])->name('admin.settings.index');
    Route::post('/admin/settings', [SettingController::class, 'update'])->name('admin.settings.update');

    // Themes
    Route::get('/admin/themes', [ThemeController::class, 'index'])->name('admin.themes.index');
    Route::post('/admin/themes', [ThemeController::class, 'update'])->name('admin.themes.update');

    // ========================================
    // МЕНЮ, ФОРМЫ И САБМИШЕНЫ — роуты теперь
    // подключаются из модулей MenuManager и FormBuilder
    // ========================================
});
