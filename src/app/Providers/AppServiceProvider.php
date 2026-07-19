<?php

namespace App\Providers;

use App\Contracts\CategoryRepositoryInterface;
use App\Contracts\FormSubmissionRepositoryInterface;
use App\Contracts\GroupRepositoryInterface;
use App\Contracts\MaterialRepositoryInterface;
use App\Contracts\MenuItemRepositoryInterface;
use App\Contracts\MenuTypeRepositoryInterface;
use App\Contracts\PermissionRepositoryInterface;
use App\Contracts\UserRepositoryInterface;
use App\Models\AccessLevel;
use App\Models\Category;
use App\Models\Group;
use App\Models\Material;
use App\Models\User;
use App\Policies\AccessLevelPolicy;
use App\Policies\CategoryPolicy;
use App\Policies\GroupPolicy;
use App\Policies\MaterialPolicy;
use App\Policies\UserPolicy;
use App\Repositories\CategoryRepository;
use App\Repositories\FormSubmissionRepository;
use App\Repositories\GroupRepository;
use App\Repositories\MaterialRepository;
use App\Repositories\MenuItemRepository;
use App\Repositories\MenuTypeRepository;
use App\Repositories\PermissionRepository;
use App\Repositories\UserRepository;
use App\Services\SettingService;
use App\Services\FormSubmissionService;
use App\Seo\Services\MetaService;
use App\Seo\Providers\MaterialSeoProvider;
use App\Seo\Providers\CategorySeoProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use App\Services\MenuService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(GroupRepositoryInterface::class, GroupRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(MaterialRepositoryInterface::class, MaterialRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(MenuTypeRepositoryInterface::class, MenuTypeRepository::class);
        $this->app->bind(MenuItemRepositoryInterface::class, MenuItemRepository::class);
        $this->app->bind(PermissionRepositoryInterface::class, PermissionRepository::class);
        $this->app->bind(FormSubmissionRepositoryInterface::class, FormSubmissionRepository::class);

        // Регистрируем SEO сервис с провайдерами
        $this->app->singleton(MetaService::class, function ($app) {
            $service = new MetaService();
            $service->registerProvider(new MaterialSeoProvider($app->make(SettingService::class)));
            $service->registerProvider(new CategorySeoProvider($app->make(SettingService::class)));
            return $service;
        });
    }

    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Group::class, GroupPolicy::class);
        Gate::policy(Material::class, MaterialPolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(AccessLevel::class, AccessLevelPolicy::class);

        Inertia::share([
            'auth' => function () {
                return [
                    'user' => auth()->user() ? auth()->user()->only(['id', 'name', 'email']) : null,
                ];
            },
            'mainMenu' => function () {
                try {
                    $menuService = app(MenuService::class);
                    $menu = $menuService->getMenuTree('main-menu');
                    return $menu;
                } catch (\Exception $e) {
                    return [];
                }
            },
            'appSettings' => function () {
                try {
                    $settingService = app(SettingService::class);
                    return $settingService->getAllSettings();
                } catch (\Exception $e) {
                    return [];
                }
            },
            'unreadCount' => function () {
                try {
                    $service = app(FormSubmissionService::class);
                    return $service->countUnread();
                } catch (\Exception $e) {
                    return 0;
                }
            },
        ]);

        // ===== RATE LIMITING =====
        // Лимит для авторизации
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        // Лимит для отправки форм
        RateLimiter::for('form-submit', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip());
        });

        // Лимит для регистрации
        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip());
        });
    }
}
