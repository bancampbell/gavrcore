<?php

declare(strict_types=1);

namespace Tests\Feature\Core;

use App\Http\Middleware\SessionIdleTimeout;
use App\Modules\UserManager\Infrastructure\Models\UserModel as User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A (docs/SECURITY.md): SessionIdleTimeout — авто-выход при простое
 * админ-сессии дольше ADMIN_IDLE_TIMEOUT минут. Гостей не трогает.
 */
class SessionIdleTimeoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_expired_idle_session_is_invalidated_and_redirected_to_login(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);
        $staleMarker = now()->subMinutes(31)->getTimestamp();

        $response = $this->actingAs($user)
            ->withSession([SessionIdleTimeout::SESSION_KEY => $staleMarker])
            ->get('/dashboard');

        $response->assertRedirect('/login');
        $this->assertGuest();
        $response->assertSessionMissing(SessionIdleTimeout::SESSION_KEY);
    }

    public function test_activity_within_timeout_passes_and_refreshes_marker(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);
        $oldMarker = now()->subMinutes(10)->getTimestamp();

        $response = $this->actingAs($user)
            ->withSession([SessionIdleTimeout::SESSION_KEY => $oldMarker])
            ->get('/dashboard');

        $response->assertOk();
        $response->assertSessionHas(
            SessionIdleTimeout::SESSION_KEY,
            fn (int $value): bool => $value > $oldMarker,
        );
    }

    public function test_first_activity_after_login_creates_marker(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
        $response->assertSessionHas(SessionIdleTimeout::SESSION_KEY);
    }

    public function test_guest_on_public_page_is_not_touched(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSessionMissing(SessionIdleTimeout::SESSION_KEY);
    }
}
