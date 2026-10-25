<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\UserManager\Infrastructure\Http\Controllers;

use App\Modules\UserManager\Infrastructure\Models\UserModel as User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Tests\TestCase;

/**
 * AUDIT-15: публичный POST /login должен писать last_login_at/last_login_ip,
 * как это делает LoginUserUseCase для /admin/login.
 */
class WebAuthControllerLoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ThrottleRequests::class);
    }

    public function test_login_updates_last_login_metadata(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);

        $this->post('/login', [
            'email' => 'user@example.com',
            'password' => 'password',
        ])->assertRedirect();

        $user->refresh();
        $this->assertNotNull($user->last_login_at);
        $this->assertSame('127.0.0.1', $user->last_login_ip);
        $this->assertAuthenticatedAs($user);
    }

    public function test_failed_login_does_not_update_last_login_metadata(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);

        $this->post('/login', [
            'email' => 'user@example.com',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertNull($user->refresh()->last_login_at);
    }
}
