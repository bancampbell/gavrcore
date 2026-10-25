<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\UserManager\Infrastructure\Http\Controllers;

use App\Modules\UserManager\Infrastructure\Models\PermissionModel;
use App\Modules\UserManager\Infrastructure\Models\UserModel as User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Tests\TestCase;

/**
 * AUDIT-14: форма /admin/login должна отвергать не-админов сразу,
 * а не пускать их до AdminMiddleware.
 */
class AdminAuthControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ThrottleRequests::class);
    }

    public function test_non_admin_login_is_rejected_with_error(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);

        $response = $this->post('/admin/login', [
            'email' => 'user@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin/login');
        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_non_admin_login_does_not_establish_session(): void
    {
        User::factory()->create(['email' => 'user@example.com']);

        $this->post('/admin/login', [
            'email' => 'user@example.com',
            'password' => 'password',
        ]);

        $this->assertGuest();
        $this->get('/admin/dashboard')->assertRedirect('/login');
    }

    public function test_admin_login_succeeds(): void
    {
        $admin = User::factory()->create(['email' => 'admin@example.com']);
        $admin->permissions()->attach(
            PermissionModel::create(['name' => 'Доступ к админке', 'key' => 'admin.access', 'group' => 'system'])->id
        );

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($admin);
    }
}
