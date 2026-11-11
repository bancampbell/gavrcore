<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\UserManager\Infrastructure\Http\Controllers;

use App\Modules\UserManager\Infrastructure\Models\UserModel as User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Throttle логина: 5 попыток/минуту, 6-я подряд неудача — 429.
 */
class AdminLoginThrottleTest extends TestCase
{
    use RefreshDatabase;

    public function test_sixth_consecutive_failed_login_is_rejected_with_429(): void
    {
        User::factory()->create(['email' => 'admin@example.com']);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/admin/login', [
                'email' => 'admin@example.com',
                'password' => 'wrong-password',
            ])->assertSessionHasErrors('email');
        }

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(429);
    }

    public function test_login_throttle_bucket_is_scoped_per_email_and_ip(): void
    {
        // Лимитер ключается email|ip: чужой email не должен упереться в чужой бакет.
        User::factory()->create(['email' => 'admin@example.com']);

        for ($i = 0; $i < 6; $i++) {
            $this->post('/admin/login', [
                'email' => 'admin@example.com',
                'password' => 'wrong-password',
            ]);
        }

        $this->post('/admin/login', [
            'email' => 'other@example.com',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');
    }
}
