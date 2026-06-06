<?php

namespace App\Providers;

use App\Contracts\CategoryRepositoryInterface;
use App\Contracts\GroupRepositoryInterface;
use App\Contracts\MaterialRepositoryInterface;
use App\Contracts\MediaRepositoryInterface;
use App\Contracts\MenuItemRepositoryInterface;
use App\Contracts\MenuTypeRepositoryInterface;
use App\Contracts\UserRepositoryInterface;
use App\Repositories\CategoryRepository;
use App\Repositories\GroupRepository;
use App\Repositories\MaterialRepository;
use App\Repositories\MediaRepository;
use App\Repositories\MenuItemRepository;
use App\Repositories\MenuTypeRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(GroupRepositoryInterface::class, GroupRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(MaterialRepositoryInterface::class, MaterialRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(MediaRepositoryInterface::class, MediaRepository::class);
        $this->app->bind(MenuTypeRepositoryInterface::class, MenuTypeRepository::class);
        $this->app->bind(MenuItemRepositoryInterface::class, MenuItemRepository::class);


    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
