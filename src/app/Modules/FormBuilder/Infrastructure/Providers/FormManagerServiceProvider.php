<?php

namespace App\Modules\FormBuilder\Infrastructure\Providers;

use App\Modules\FormBuilder\Domain\Repositories\FormRepositoryInterface;
use App\Modules\FormBuilder\Domain\Repositories\FormSubmissionRepositoryInterface;
use App\Modules\FormBuilder\Domain\Services\ClockInterface;
use App\Modules\FormBuilder\Domain\Services\SlugGeneratorInterface;
use App\Modules\FormBuilder\Infrastructure\Repositories\FormRepository;
use App\Modules\FormBuilder\Infrastructure\Repositories\FormSubmissionRepository;
use App\Modules\FormBuilder\Infrastructure\Services\SlugGenerator;
use App\Modules\FormBuilder\Infrastructure\Services\SystemClock;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class FormManagerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../Config/form-manager.php',
            'form-manager'
        );

        $this->app->bind(FormRepositoryInterface::class, FormRepository::class);
        $this->app->bind(FormSubmissionRepositoryInterface::class, FormSubmissionRepository::class);
        $this->app->bind(SlugGeneratorInterface::class, SlugGenerator::class);
        $this->app->bind(ClockInterface::class, SystemClock::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../../Routes/admin.php');

        $this->app->booted(function () {
            $this->loadRoutesFrom(__DIR__ . '/../../Routes/api.php');
        });

        $this->loadMigrationsFrom(__DIR__ . '/../../Database/Migrations');
        $this->loadViewsFrom(__DIR__ . '/../../Infrastructure/views', 'form-manager');

        RateLimiter::for('form-submit', function ($request) {
            return Limit::perMinute(10)->by($request->ip());
        });
    }
}