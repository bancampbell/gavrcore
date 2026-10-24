<?php

namespace App\Modules\UserDashboard\Infrastructure\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

final class UserDashboardServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Роуты модуля
        Route::middleware('web')->group(__DIR__.'/../../Routes/web.php');
    }
}
