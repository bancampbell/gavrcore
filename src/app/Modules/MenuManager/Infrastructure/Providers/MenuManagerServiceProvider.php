<?php

namespace App\Modules\MenuManager\Infrastructure\Providers;

use App\Modules\MenuManager\Domain\Repositories\MenuItemRepositoryInterface;
use App\Modules\MenuManager\Domain\Repositories\MenuTypeRepositoryInterface;
use App\Modules\MenuManager\Domain\Services\MenuTreeBuilderInterface;
use App\Modules\MenuManager\Domain\Services\SlugGeneratorInterface;
use App\Modules\MenuManager\Infrastructure\Http\Middleware\ShareMenuMiddleware;
use App\Modules\MenuManager\Infrastructure\Models\MenuItemModel;
use App\Modules\MenuManager\Infrastructure\Models\MenuTypeModel;
use App\Modules\MenuManager\Infrastructure\Policies\MenuItemPolicy;
use App\Modules\MenuManager\Infrastructure\Policies\MenuTypePolicy;
use App\Modules\MenuManager\Infrastructure\Repositories\MenuItemRepository;
use App\Modules\MenuManager\Infrastructure\Repositories\MenuTypeRepository;
use App\Modules\MenuManager\Infrastructure\Services\MenuTreeBuilder;
use App\Modules\MenuManager\Infrastructure\Services\SlugGenerator;
use Illuminate\Contracts\Http\Kernel;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class MenuManagerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(MenuTypeRepositoryInterface::class, MenuTypeRepository::class);
        $this->app->bind(MenuItemRepositoryInterface::class, MenuItemRepository::class);
        $this->app->bind(SlugGeneratorInterface::class, SlugGenerator::class);
        $this->app->bind(MenuTreeBuilderInterface::class, MenuTreeBuilder::class);
        $this->mergeConfigFrom(__DIR__ . '/../../Config/menu-manager.php', 'menu-manager');
    }

    public function boot(Kernel $kernel): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../Database/Migrations');
        $this->loadViewsFrom(__DIR__ . '/../../Infrastructure/views', 'menu-manager');
        $kernel->appendMiddlewareToGroup('web', ShareMenuMiddleware::class);

        $this->registerPolicies();
        $this->registerGates();

        $this->mapAdminRoutes();
        $this->mapWebRoutes();
    }

    protected function registerPolicies(): void
    {
        Gate::policy(MenuTypeModel::class, MenuTypePolicy::class);
        Gate::policy(MenuItemModel::class, MenuItemPolicy::class);
    }

    protected function registerGates(): void
    {
        Gate::define('manage menus', function ($user) {
            return $user !== null && method_exists($user, 'isAdmin') && $user->isAdmin();
        });
    }

    protected function mapAdminRoutes(): void
    {
        Route::middleware(['web', 'auth'])->prefix('admin/menu')
            ->group(__DIR__ . '/../../Routes/admin.php');
    }

    protected function mapWebRoutes(): void
    {
        Route::middleware('web')
            ->group(__DIR__ . '/../../Routes/web.php');
    }
}
