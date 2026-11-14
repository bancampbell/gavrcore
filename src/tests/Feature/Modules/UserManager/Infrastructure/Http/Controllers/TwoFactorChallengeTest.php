<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\UserManager\Infrastructure\Http\Controllers;

use App\Modules\UserManager\Application\Services\TotpService;
use App\Modules\UserManager\Infrastructure\Http\Controllers\TwoFactorChallengeController;
use App\Modules\UserManager\Infrastructure\Models\PermissionModel;
use App\Modules\UserManager\Infrastructure\Models\UserModel as User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Tests\TestCase;

/**
 * 2FA при логине (часть 1б): верный пароль + totp_enabled → метка ожидания
 * в сессии и редирект на ввод кода; эндпоинт /2fa/verify принимает TOTP
 * (6 цифр) или резервный код (XXXX-XXXX-XXXX-XXXX, с дефисами/без), резервный
 * сгорает, без метки — 403, throttle 5/мин (user_id|ip), 6-я попытка — 429.
 */
class TwoFactorChallengeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ThrottleRequests::class);
    }

    public function test_login_with_totp_enabled_requires_second_factor(): void
    {
        [$user] = $this->createTotpUser(['email' => 'user@example.com']);

        $response = $this->post('/login', [
            'email' => 'user@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/2fa/verify');
        $this->assertGuest();
        $response->assertSessionHas(TwoFactorChallengeController::SESSION_KEY, [
            'user_id' => $user->id,
            'guard' => 'web',
            'remember' => false,
            'redirect' => 'http://localhost/dashboard',
        ]);
    }

    public function test_pending_marker_keeps_remember_flag(): void
    {
        $this->createTotpUser(['email' => 'remember@example.com']);

        $response = $this->post('/login', [
            'email' => 'remember@example.com',
            'password' => 'password',
            'remember' => '1',
        ]);

        $response->assertSessionHas(TwoFactorChallengeController::SESSION_KEY.'.remember', true);
    }

    public function test_valid_totp_code_authenticates_and_clears_marker(): void
    {
        [$user, $secret] = $this->createTotpUser(['email' => 'user@example.com']);

        $this->post('/login', [
            'email' => 'user@example.com',
            'password' => 'password',
        ])->assertRedirect('/2fa/verify');

        $code = app(TotpService::class)->generateCode($secret);

        $response = $this->post('/2fa/verify', ['code' => $code]);

        $response->assertRedirect('http://localhost/dashboard');
        $this->assertAuthenticatedAs($user);
        $response->assertSessionMissing(TwoFactorChallengeController::SESSION_KEY);
    }

    public function test_invalid_code_is_rejected_and_marker_remains(): void
    {
        [, $secret] = $this->createTotpUser(['email' => 'user@example.com']);

        $this->post('/login', [
            'email' => 'user@example.com',
            'password' => 'password',
        ]);

        // Гарантированно невалидный код: правильный секрет, окно за 5 минут
        // до текущего (допуск verify — только ±1 окно).
        $wrongCode = app(TotpService::class)->generateCode($secret, time() - 300);

        $response = $this->post('/2fa/verify', ['code' => $wrongCode]);

        $response->assertSessionHasErrors('code');
        $this->assertGuest();
        $response->assertSessionHas(TwoFactorChallengeController::SESSION_KEY);
    }

    public function test_recovery_code_authenticates_and_burns(): void
    {
        [$user, , $codes] = $this->createTotpUser(['email' => 'user@example.com']);

        $this->post('/login', [
            'email' => 'user@example.com',
            'password' => 'password',
        ]);

        // Ввод резервного кода без дефисов и в нижнем регистре — нормализация на сервисе
        $response = $this->post('/2fa/verify', [
            'code' => strtolower(str_replace('-', '', $codes[0])),
        ]);

        $response->assertRedirect('http://localhost/dashboard');
        $this->assertAuthenticatedAs($user);
        $response->assertSessionMissing(TwoFactorChallengeController::SESSION_KEY);
        $this->assertCount(7, $user->fresh()->totp_recovery_codes);

        // Тот же код второй раз (новый логин) — не пускает
        $this->post('/logout');

        $this->post('/login', [
            'email' => 'user@example.com',
            'password' => 'password',
        ])->assertRedirect('/2fa/verify');

        $response = $this->post('/2fa/verify', ['code' => $codes[0]]);

        $response->assertSessionHasErrors('code');
        $this->assertGuest();
        $response->assertSessionHas(TwoFactorChallengeController::SESSION_KEY);
    }

    public function test_verify_without_pending_marker_is_forbidden(): void
    {
        $this->post('/2fa/verify', ['code' => '123456'])->assertForbidden();
    }

    public function test_sixth_verify_attempt_is_throttled_with_429(): void
    {
        [, $secret] = $this->createTotpUser(['email' => 'throttle@example.com']);
        $this->withMiddleware(ThrottleRequests::class);

        $this->post('/login', [
            'email' => 'throttle@example.com',
            'password' => 'password',
        ])->assertRedirect('/2fa/verify');

        $wrongCode = app(TotpService::class)->generateCode($secret, time() - 300);

        for ($i = 0; $i < 5; $i++) {
            $this->post('/2fa/verify', ['code' => $wrongCode])->assertSessionHasErrors('code');
        }

        $this->assertGuest();

        $this->post('/2fa/verify', ['code' => $wrongCode])->assertStatus(429);

        // Лимитер ключается user_id|ip: чужой пользователь в свой бакет не упирается
        $this->createTotpUser(['email' => 'other@example.com']);

        $this->post('/login', [
            'email' => 'other@example.com',
            'password' => 'password',
        ])->assertRedirect('/2fa/verify');

        $this->post('/2fa/verify', ['code' => $wrongCode])->assertSessionHasErrors('code');
    }

    public function test_admin_login_with_totp_enabled_requires_second_factor(): void
    {
        [$admin, $secret] = $this->createTotpUser(['email' => 'admin@example.com']);
        $this->grantAdminAccess($admin);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/2fa/verify');
        $this->assertGuest();
        $response->assertSessionHas(TwoFactorChallengeController::SESSION_KEY.'.user_id', $admin->id);
        $response->assertSessionHas(TwoFactorChallengeController::SESSION_KEY.'.redirect', '/admin/dashboard');

        // Второй фактор завершает вход в админку
        $code = app(TotpService::class)->generateCode($secret);

        $response = $this->post('/2fa/verify', ['code' => $code]);

        $response->assertRedirect('/admin/dashboard');
        $this->assertAuthenticatedAs($admin);
    }

    public function test_login_without_2fa_has_no_challenge_step(): void
    {
        $user = User::factory()->create(['email' => 'plain@example.com']);

        $response = $this->post('/login', [
            'email' => 'plain@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('http://localhost/dashboard');
        $this->assertAuthenticatedAs($user);
        $response->assertSessionMissing(TwoFactorChallengeController::SESSION_KEY);
    }

    public function test_login_with_pending_totp_secret_has_no_challenge_step(): void
    {
        // Секрет есть (между enable и confirm), но totp_enabled=false — логин обычный
        $user = User::factory()->create(['email' => 'pending@example.com']);
        $user->forceFill(['totp_secret' => app(TotpService::class)->generateSecret()])->save();

        $response = $this->post('/login', [
            'email' => 'pending@example.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('http://localhost/dashboard');
        $this->assertAuthenticatedAs($user);
        $response->assertSessionMissing(TwoFactorChallengeController::SESSION_KEY);
    }

    /**
     * Пользователь с полностью включённой 2FA (флаг, секрет, хеши резервных кодов).
     *
     * @return array{User, string, array<int, string>}
     */
    private function createTotpUser(array $attributes): array
    {
        $totp = app(TotpService::class);
        $secret = $totp->generateSecret();
        $codes = $totp->generateRecoveryCodes();

        $user = User::factory()->create($attributes);
        $user->forceFill([
            'totp_secret' => $secret,
            'totp_enabled' => true,
            'totp_recovery_codes' => array_map(
                fn (string $plain) => $totp->hashRecoveryCode($plain),
                $codes,
            ),
        ])->save();

        return [$user->fresh(), $secret, $codes];
    }

    private function grantAdminAccess(User $user): void
    {
        $user->permissions()->attach(
            PermissionModel::create(['name' => 'Доступ к админке', 'key' => 'admin.access', 'group' => 'system'])->id
        );
    }
}
