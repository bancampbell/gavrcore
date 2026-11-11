<?php

namespace App\Modules\CaptchaManager\Infrastructure\Providers;

use Illuminate\Support\ServiceProvider;

final class CaptchaManagerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Роуты не нужны: модуль предоставляет драйверы капчи потребителям (FormBuilder).
    }
}
