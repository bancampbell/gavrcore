<?php

declare(strict_types=1);

namespace App\Modules\GalleryManager\Infrastructure\Providers;

use App\Modules\GalleryManager\Domain\Repositories\GalleryRepositoryInterface;
use App\Modules\GalleryManager\Domain\Services\ImageStorageServiceInterface;
use App\Modules\GalleryManager\Infrastructure\Repositories\GalleryRepository;
use App\Modules\GalleryManager\Infrastructure\Services\LocalImageStorageService;
use Illuminate\Support\ServiceProvider;

class GalleryManagerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(GalleryRepositoryInterface::class, GalleryRepository::class);
        $this->app->bind(ImageStorageServiceInterface::class, LocalImageStorageService::class);

        $this->mergeConfigFrom(
            __DIR__ . '/../../Config/gallery-manager.php',
            'gallery-manager'
        );
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../Database/Migrations');
        $this->loadRoutesFrom(__DIR__ . '/../../Routes/admin.php');
        $this->loadRoutesFrom(__DIR__ . '/../../Routes/web.php');
    }
}
