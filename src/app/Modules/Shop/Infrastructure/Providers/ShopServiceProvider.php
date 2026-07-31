<?php

namespace Modules\Shop\Infrastructure\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\Shop\Domain\Repositories\CartRepositoryInterface;
use Modules\Shop\Domain\Repositories\OrderRepositoryInterface;
use Modules\Shop\Domain\Repositories\ProductRepositoryInterface;
use Modules\Shop\Infrastructure\Repositories\OrderRepository;
use Modules\Shop\Infrastructure\Repositories\ProductRepository;
use Modules\Shop\Infrastructure\Repositories\SessionCartRepository;

class ShopServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(CartRepositoryInterface::class, SessionCartRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../../Routes/web.php');
        $this->loadMigrationsFrom(__DIR__ . '/../../Database/Migrations');
        $this->registerPolicies();
    }

    private function registerPolicies(): void
    {
        Gate::define('manage-shop', fn($user) => $user->hasRole('admin'));
    }
}
