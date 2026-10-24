<?php

namespace App\Modules\AdminDashboard\Infrastructure\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

final class AdminDashboardServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Роуты модуля
        Route::middleware('web')->group(__DIR__.'/../../Routes/admin.php');
    }
}
