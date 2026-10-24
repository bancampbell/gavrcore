<?php

namespace App\Modules\SeoManager\Infrastructure\Providers;

use App\Modules\CategoryManager\Domain\Events\CategorySaved;
use App\Modules\MaterialManager\Domain\Events\MaterialSaved;
use App\Modules\SeoManager\Application\Services\MetaService;
use App\Modules\SeoManager\Infrastructure\Listeners\ClearSitemapCacheListener;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

final class SeoManagerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // SEO сервис с механизмом провайдеров — провайдеры регистрируются
        // самими модулями (например, CategoryManager через $app->resolving)
        $this->app->singleton(MetaService::class);
    }

    public function boot(): void
    {
        // Роуты модуля
        Route::middleware('web')->group(__DIR__.'/../../Routes/web.php');

        $this->registerListeners();
    }

    private function registerListeners(): void
    {
        Event::listen(
            MaterialSaved::class,
            [ClearSitemapCacheListener::class, 'handleMaterialSaved']
        );

        Event::listen(
            CategorySaved::class,
            [ClearSitemapCacheListener::class, 'handleCategorySaved']
        );
    }
}
