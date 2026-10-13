<?php

namespace App\Modules\SettingManager\Infrastructure\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

final class SettingManagerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Миграции модуля
        $this->loadMigrationsFrom(__DIR__.'/../../Database/Migrations');

        // Роуты модуля
        Route::middleware('web')->group(__DIR__.'/../../Routes/admin.php');
    }
}
