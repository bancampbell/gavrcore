<?php

namespace Modules\MediaManager\Infrastructure\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\MediaManager\Domain\Events\FolderCreated;
use Modules\MediaManager\Domain\Events\MediaCopied;
use Modules\MediaManager\Domain\Events\MediaDeleted;
use Modules\MediaManager\Domain\Events\MediaRenamed;
use Modules\MediaManager\Domain\Events\MediaUploaded;
use Modules\MediaManager\Domain\Repositories\MediaRepositoryInterface;
use Modules\MediaManager\Infrastructure\Console\Commands\MediaAuditCommand;
use Modules\MediaManager\Infrastructure\Listeners\LogMediaActivityListener;
use Modules\MediaManager\Infrastructure\Repositories\MediaRepository;

class MediaManagerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            MediaRepositoryInterface::class,
            MediaRepository::class
        );

        $this->mergeConfigFrom(
            __DIR__ . '/../../Config/media-manager.php',
            'MediaManager'
        );
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../../Routes/web.php');
        $this->loadMigrationsFrom(__DIR__ . '/../../Database/Migrations');
        $this->registerPolicies();
        $this->registerAuditListeners();
        $this->registerCommands();
    }

    protected function registerPolicies(): void
    {
        Gate::define('manage-media', function ($user) {
            return method_exists($user, 'isAdmin')
                ? $user->isAdmin()
                : ($user->is_admin ?? false);
        });
    }

    protected function registerAuditListeners(): void
    {
        Event::listen(MediaUploaded::class, LogMediaActivityListener::class);
        Event::listen(MediaRenamed::class, LogMediaActivityListener::class);
        Event::listen(MediaDeleted::class, LogMediaActivityListener::class);
        Event::listen(MediaCopied::class, LogMediaActivityListener::class);
        Event::listen(FolderCreated::class, LogMediaActivityListener::class);
    }

    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MediaAuditCommand::class,
            ]);
        }
    }
}
