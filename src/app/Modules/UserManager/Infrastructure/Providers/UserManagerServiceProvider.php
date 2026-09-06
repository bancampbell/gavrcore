<?php

namespace App\Modules\UserManager\Infrastructure\Providers;

use App\Modules\UserManager\Domain\Entities\AccessLevel;
use App\Modules\UserManager\Domain\Entities\Group;
use App\Modules\UserManager\Domain\Entities\User;
use App\Modules\UserManager\Domain\Repositories\AccessLevelRepositoryInterface;
use App\Modules\UserManager\Domain\Repositories\GroupRepositoryInterface;
use App\Modules\UserManager\Domain\Repositories\PermissionRepositoryInterface;
use App\Modules\UserManager\Domain\Repositories\UserRepositoryInterface;
use App\Modules\UserManager\Domain\Services\SlugGeneratorInterface;
use App\Modules\UserManager\Infrastructure\Policies\AccessLevelPolicy;
use App\Modules\UserManager\Infrastructure\Policies\GroupPolicy;
use App\Modules\UserManager\Infrastructure\Policies\UserPolicy;
use App\Modules\UserManager\Infrastructure\Repositories\AccessLevelRepository;
use App\Modules\UserManager\Infrastructure\Repositories\GroupRepository;
use App\Modules\UserManager\Infrastructure\Repositories\PermissionRepository;
use App\Modules\UserManager\Infrastructure\Repositories\UserRepository;
use App\Modules\UserManager\Infrastructure\Services\SlugGenerator;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

final class UserManagerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../Config/user-manager.php', 'user-manager');

        // Репозитории
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(GroupRepositoryInterface::class, GroupRepository::class);
        $this->app->bind(PermissionRepositoryInterface::class, PermissionRepository::class);
        $this->app->bind(AccessLevelRepositoryInterface::class, AccessLevelRepository::class);

        // Доменные сервисы
        $this->app->bind(SlugGeneratorInterface::class, SlugGenerator::class);
    }

    public function boot(): void
    {
        // Именованные лимитеры для throttle:login / throttle:register.
        // Роуты модуля ссылаются на эти имена — если лимитер не зарегистрирован,
        // ThrottleRequests бросает RuntimeException и весь auth падает с 500.
        // Регистрируем здесь, а не в bootstrap приложения: модуль должен быть
        // самодостаточным. RateLimiter::for переопределяет существующее
        // определение, конфликта с приложением нет.
        RateLimiter::for('login', function (Request $request) {
            // Email нормализуем: регистрозависимый ключ дал бы два бакета
            // для одного аккаунта (см. нормализацию в Email VO).
            $email = mb_strtolower(trim((string) $request->input('email')));

            return Limit::perMinute(5)->by($email.'|'.$request->ip());
        });

        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip());
        });

        // Миграции модуля
        $this->loadMigrationsFrom(__DIR__.'/../../Database/Migrations');

        // Роуты модуля
        Route::middleware('web')->group(__DIR__.'/../../Routes/web.php');
        Route::middleware('web')->group(__DIR__.'/../../Routes/admin.php');
        Route::middleware('api')->prefix('api')->group(__DIR__.'/../../Routes/api.php');

        // Политики (привязаны к доменным сущностям)
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Group::class, GroupPolicy::class);
        Gate::policy(AccessLevel::class, AccessLevelPolicy::class);
    }
}
