<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\UserManager\Infrastructure\Http\Controllers;

use App\Modules\UserManager\Infrastructure\Http\Middleware\AdminMiddleware;
use App\Modules\UserManager\Infrastructure\Models\UserModel as User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AdminUsersGroupsFiltersPropsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
        Gate::before(fn (User $user) => $user->id === $this->admin->id ? true : null);
    }

    public function test_users_index_casts_string_boolean_filters_to_boolean_props(): void
    {
        // После F5 фильтры приходят query-строками ('true'/'false') — в пропсы
        // Inertia они должны уходить настоящими boolean, иначе select статуса
        // с boolean-опциями не сматчивается и фильтр визуально сбрасывается.
        $this->actingAs($this->admin)
            ->withoutMiddleware(AdminMiddleware::class)
            ->get(route('admin.users.index', ['blocked' => 'true', 'activated' => 'false']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('UserManager/Users/Index', false)
                ->where('filters.blocked', fn ($value) => $value === true)
                ->where('filters.activated', fn ($value) => $value === false)
            );
    }

    public function test_groups_index_casts_string_boolean_filters_to_boolean_props(): void
    {
        $this->actingAs($this->admin)
            ->withoutMiddleware(AdminMiddleware::class)
            ->get(route('admin.groups.index', ['status' => 'false']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('UserManager/Groups/Index', false)
                ->where('filters.status', fn ($value) => $value === false)
            );
    }
}
