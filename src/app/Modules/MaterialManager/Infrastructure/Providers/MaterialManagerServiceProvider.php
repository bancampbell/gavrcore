<?php

namespace App\Modules\MaterialManager\Infrastructure\Providers;

use App\Modules\MaterialManager\Domain\Repositories\MaterialRepositoryInterface;
use App\Modules\MaterialManager\Domain\Services\CategoryServiceInterface;
use App\Modules\MaterialManager\Domain\Services\ClockInterface;
use App\Modules\MaterialManager\Domain\Services\ContentParserInterface;
use App\Modules\MaterialManager\Domain\Services\FormServiceInterface;
use App\Modules\MaterialManager\Domain\Services\SlugGeneratorInterface;
use App\Modules\MaterialManager\Domain\Entities\Material;
use App\Modules\MaterialManager\Infrastructure\Policies\MaterialPolicy;
use App\Modules\MaterialManager\Infrastructure\Repositories\MaterialRepository;
use App\Modules\MaterialManager\Infrastructure\Services\CategoryService;
use App\Modules\MaterialManager\Infrastructure\Services\ContentParser;
use App\Modules\MaterialManager\Infrastructure\Services\FormService;
use App\Modules\MaterialManager\Infrastructure\Services\SlugGenerator;
use App\Modules\MaterialManager\Infrastructure\Services\SystemClock;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class MaterialManagerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            MaterialRepositoryInterface::class,
            MaterialRepository::class
        );

        $this->app->bind(
            ContentParserInterface::class,
            ContentParser::class
        );

        $this->app->bind(
            CategoryServiceInterface::class,
            CategoryService::class
        );

        $this->app->bind(
            FormServiceInterface::class,
            FormService::class
        );

        $this->app->bind(
            SlugGeneratorInterface::class,
            SlugGenerator::class
        );

        $this->app->bind(
            ClockInterface::class,
            SystemClock::class
        );

        $this->mergeConfigFrom(
            __DIR__ . '/../../Config/material-manager.php',
            'material-manager'
        );
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../../Routes/admin.php');
        $this->app->booted(function () {
            $this->loadRoutesFrom(__DIR__ . '/../../Routes/web.php');
        });

        $this->loadViewsFrom(__DIR__ . '/../../Resources/views', 'material-manager');

        $this->loadMigrationsFrom(__DIR__ . '/../../Database/Migrations');

        $this->registerPolicies();

        $this->registerListeners();

        $this->registerRateLimiters();
    }

    private function registerPolicies(): void
    {
        Gate::policy(Material::class, MaterialPolicy::class);
    }

    private function registerListeners(): void
    {
        Event::listen(
            \App\Modules\MaterialManager\Domain\Events\MaterialCreated::class,
            [\App\Modules\MaterialManager\Infrastructure\Listeners\LogMaterialActivityListener::class, 'handleMaterialCreated']
        );

        Event::listen(
            \App\Modules\MaterialManager\Domain\Events\MaterialUpdated::class,
            [\App\Modules\MaterialManager\Infrastructure\Listeners\LogMaterialActivityListener::class, 'handleMaterialUpdated']
        );

        Event::listen(
            \App\Modules\MaterialManager\Domain\Events\MaterialDeleted::class,
            [\App\Modules\MaterialManager\Infrastructure\Listeners\LogMaterialActivityListener::class, 'handleMaterialDeleted']
        );

        Event::listen(
            \App\Modules\MaterialManager\Domain\Events\MaterialRestored::class,
            [\App\Modules\MaterialManager\Infrastructure\Listeners\LogMaterialActivityListener::class, 'handleMaterialRestored']
        );

        Event::listen(
            \App\Modules\MaterialManager\Domain\Events\MaterialForceDeleted::class,
            [\App\Modules\MaterialManager\Infrastructure\Listeners\LogMaterialActivityListener::class, 'handleMaterialForceDeleted']
        );

        Event::listen(
            \App\Modules\MaterialManager\Domain\Events\MaterialPublished::class,
            [\App\Modules\MaterialManager\Infrastructure\Listeners\LogMaterialActivityListener::class, 'handleMaterialPublished']
        );

        Event::listen(
            \App\Modules\MaterialManager\Domain\Events\MaterialUnpublished::class,
            [\App\Modules\MaterialManager\Infrastructure\Listeners\LogMaterialActivityListener::class, 'handleMaterialUnpublished']
        );

        Event::listen(
            \App\Modules\MaterialManager\Domain\Events\MaterialHomepageToggled::class,
            [\App\Modules\MaterialManager\Infrastructure\Listeners\LogMaterialActivityListener::class, 'handleMaterialHomepageToggled']
        );
    }

    private function registerRateLimiters(): void
    {
        RateLimiter::for('material-bulk', function (Request $request) {
            $userId = $request->user()?->id;
            return Limit::perMinute(30)->by($userId ? 'user:' . $userId : 'ip:' . $request->ip());
        });

        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });
    }
}
