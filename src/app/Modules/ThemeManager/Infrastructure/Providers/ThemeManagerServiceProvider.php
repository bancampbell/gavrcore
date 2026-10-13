<?php

namespace App\Modules\ThemeManager\Infrastructure\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

final class ThemeManagerServiceProvider extends ServiceProvider
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
