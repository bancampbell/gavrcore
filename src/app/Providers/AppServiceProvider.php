<?php

namespace App\Providers;

use App\Services\SettingService;
use App\Seo\Services\MetaService;
use App\Seo\Providers\CategorySeoProvider;
use App\Modules\MenuManager\Application\UseCases\GetMenuTreeUseCase;
use App\Modules\FormBuilder\Application\UseCases\GetUnreadSubmissionsCountUseCase;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Репозитории User/Group/Permission/AccessLevel и политики
        // зарегистрированы в модуле UserManager (UserManagerServiceProvider)

        // Регистрируем SEO сервис с провайдерами
        $this->app->singleton(MetaService::class, function ($app) {
            $service = new MetaService();
            $service->registerProvider(new CategorySeoProvider($app->make(SettingService::class)));
            return $service;
        });
    }

    public function boot(): void
    {
        Inertia::share([
            'auth' => function () {
                return [
                    'user' => auth()->user() ? auth()->user()->only(['id', 'name', 'email']) : null,
                ];
            },
            'mainMenu' => function () {
                try {
                    $useCase = app(GetMenuTreeUseCase::class);
                    return $useCase->execute('main-menu');
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
                    $useCase = app(GetUnreadSubmissionsCountUseCase::class);
                    return $useCase->execute();
                } catch (\Exception $e) {
                    return 0;
                }
            },
        ]);

        // ===== RATE LIMITING =====
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        RateLimiter::for('form-submit', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip());
        });

        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip());
        });
    }
}
