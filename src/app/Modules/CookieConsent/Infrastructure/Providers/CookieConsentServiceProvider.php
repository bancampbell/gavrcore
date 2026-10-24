<?php

namespace App\Modules\CookieConsent\Infrastructure\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

final class CookieConsentServiceProvider extends ServiceProvider
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
