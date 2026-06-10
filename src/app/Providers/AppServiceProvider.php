<?php

namespace App\Providers;

use App\Contracts\CategoryRepositoryInterface;
use App\Contracts\GroupRepositoryInterface;
use App\Contracts\MaterialRepositoryInterface;
use App\Contracts\MediaRepositoryInterface;
use App\Contracts\MenuItemRepositoryInterface;
use App\Contracts\MenuTypeRepositoryInterface;
use App\Contracts\PermissionRepositoryInterface;
use App\Contracts\UserRepositoryInterface;
use App\Repositories\CategoryRepository;
use App\Repositories\GroupRepository;
use App\Repositories\MaterialRepository;
use App\Repositories\MediaRepository;
use App\Repositories\MenuItemRepository;
use App\Repositories\MenuTypeRepository;
use App\Repositories\PermissionRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

// Импорт моделей
use App\Models\User;
use App\Models\Group;
use App\Models\Material;
use App\Models\Category;
use App\Models\AccessLevel;

// Импорт политик
use App\Policies\UserPolicy;
use App\Policies\GroupPolicy;
use App\Policies\MaterialPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\AccessLevelPolicy;

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
        $this->app->bind(MenuTypeRepositoryInterface::class, MenuTypeRepository::class);
        $this->app->bind(MenuItemRepositoryInterface::class, MenuItemRepository::class);
        $this->app->bind(PermissionRepositoryInterface::class, PermissionRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Регистрация политик
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Group::class, GroupPolicy::class);
        Gate::policy(Material::class, MaterialPolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(AccessLevel::class, AccessLevelPolicy::class);
    }
}
