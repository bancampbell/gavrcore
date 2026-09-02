<?php

namespace App\Modules\CategoryManager\Infrastructure\Providers;

use App\Modules\CategoryManager\Domain\Repositories\CategoryRepositoryInterface;
use App\Modules\CategoryManager\Domain\Services\CategoryTreeBuilderInterface;
use App\Modules\CategoryManager\Infrastructure\Models\CategoryModel;
use App\Modules\CategoryManager\Infrastructure\Policies\CategoryPolicy;
use App\Modules\CategoryManager\Infrastructure\Repositories\CategoryRepository;
use App\Modules\CategoryManager\Infrastructure\Services\CategoryTreeBuilder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class CategoryManagerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(CategoryTreeBuilderInterface::class, CategoryTreeBuilder::class);

        $this->mergeConfigFrom(
            __DIR__ . '/../../Config/category-manager.php',
            'category-manager'
        );
    }

    public function boot(): void
    {
        Gate::policy(CategoryModel::class, CategoryPolicy::class);

        $this->loadMigrationsFrom(__DIR__ . '/../../Database/Migrations');

        Route::middleware(['web', 'auth'])
            ->prefix('admin')
            ->name('admin.')
            ->group(__DIR__ . '/../../Routes/admin.php');

        Route::middleware('web')
            ->group(__DIR__ . '/../../Routes/web.php');
    }
}
