<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\UserManager\Infrastructure\Http\Middleware;

use App\Modules\UserManager\Infrastructure\Models\PermissionModel;
use App\Modules\UserManager\Infrastructure\Models\UserModel as User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * AUDIT-18: AdminMiddleware — гость редиректится на /login,
 * аутентифицированный пользователь без прав получает 403.
 */
class AdminMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/login');
    }

    public function test_non_admin_user_gets_forbidden_inertia_page(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);

        $response = $this->actingAs($user)->get('/admin/dashboard');

        $response->assertForbidden()
            ->assertInertia(fn (Assert $page) => $page->component('Errors/Forbidden', false));
    }

    public function test_non_admin_user_gets_forbidden_json_for_ajax(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);

        $response = $this->actingAs($user)
            ->withHeader('Accept', 'application/json')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->get('/admin/dashboard');

        $response->assertForbidden()
            ->assertJsonPath('message', 'Доступ запрещён');
    }

    public function test_admin_user_passes_through(): void
    {
        $admin = User::factory()->create(['email' => 'admin@example.com']);
        $admin->permissions()->attach(
            PermissionModel::create(['name' => 'Доступ к админке', 'key' => 'admin.access', 'group' => 'system'])->id
        );

        $this->actingAs($admin)->get('/admin/dashboard')->assertOk();
    }
}
