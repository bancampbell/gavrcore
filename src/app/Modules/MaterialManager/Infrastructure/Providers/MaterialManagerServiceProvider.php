<?php

namespace App\Modules\MaterialManager\Infrastructure\Providers;

use App\Modules\MaterialManager\Domain\Entities\Material;
use App\Modules\MaterialManager\Domain\Events\MaterialCreated;
use App\Modules\MaterialManager\Domain\Events\MaterialDeleted;
use App\Modules\MaterialManager\Domain\Events\MaterialForceDeleted;
use App\Modules\MaterialManager\Domain\Events\MaterialHomepageToggled;
use App\Modules\MaterialManager\Domain\Events\MaterialPublished;
use App\Modules\MaterialManager\Domain\Events\MaterialRestored;
use App\Modules\MaterialManager\Domain\Events\MaterialUnpublished;
use App\Modules\MaterialManager\Domain\Events\MaterialUpdated;
use App\Modules\MaterialManager\Domain\Repositories\MaterialRepositoryInterface;
use App\Modules\MaterialManager\Domain\Services\CategoryServiceInterface;
use App\Modules\MaterialManager\Domain\Services\ClockInterface;
use App\Modules\MaterialManager\Domain\Services\ContentParserInterface;
use App\Modules\MaterialManager\Domain\Services\FormServiceInterface;
use App\Modules\MaterialManager\Domain\Services\SlugGeneratorInterface;
use App\Modules\MaterialManager\Infrastructure\Listeners\LogMaterialActivityListener;
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
            __DIR__.'/../../Config/material-manager.php',
            'material-manager'
        );
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../../Routes/admin.php');
        $this->app->booted(function () {
            $this->loadRoutesFrom(__DIR__.'/../../Routes/web.php');
        });

        $this->loadViewsFrom(__DIR__.'/../../Resources/views', 'material-manager');

        $this->loadMigrationsFrom(__DIR__.'/../../Database/Migrations');

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
            MaterialCreated::class,
            [LogMaterialActivityListener::class, 'handleMaterialCreated']
        );

        Event::listen(
            MaterialUpdated::class,
            [LogMaterialActivityListener::class, 'handleMaterialUpdated']
        );

        Event::listen(
            MaterialDeleted::class,
            [LogMaterialActivityListener::class, 'handleMaterialDeleted']
        );

        Event::listen(
            MaterialRestored::class,
            [LogMaterialActivityListener::class, 'handleMaterialRestored']
        );

        Event::listen(
            MaterialForceDeleted::class,
            [LogMaterialActivityListener::class, 'handleMaterialForceDeleted']
        );

        Event::listen(
            MaterialPublished::class,
            [LogMaterialActivityListener::class, 'handleMaterialPublished']
        );

        Event::listen(
            MaterialUnpublished::class,
            [LogMaterialActivityListener::class, 'handleMaterialUnpublished']
        );

        Event::listen(
            MaterialHomepageToggled::class,
            [LogMaterialActivityListener::class, 'handleMaterialHomepageToggled']
        );
    }

    private function registerRateLimiters(): void
    {
        RateLimiter::for('material-bulk', function (Request $request) {
            $userId = $request->user()?->id;

            return Limit::perMinute(30)->by($userId ? 'user:'.$userId : 'ip:'.$request->ip());
        });
    }
}
