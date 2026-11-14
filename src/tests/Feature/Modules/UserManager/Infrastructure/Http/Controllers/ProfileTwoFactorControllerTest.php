<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\UserManager\Infrastructure\Http\Controllers;

use App\Modules\UserManager\Application\Services\TotpService;
use App\Modules\UserManager\Infrastructure\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTwoFactorControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_gets_401_on_all_three_endpoints(): void
    {
        $this->postJson('/profile/2fa/enable')->assertUnauthorized();
        $this->postJson('/profile/2fa/confirm', ['code' => '123456'])->assertUnauthorized();
        $this->postJson('/profile/2fa/disable', ['current_password' => 'password'])->assertUnauthorized();
    }

    public function test_enable_returns_otpauth_url_and_does_not_enable_flag(): void
    {
        $user = UserModel::factory()->create();

        $response = $this->actingAs($user)->postJson('/profile/2fa/enable');

        $response->assertOk()
            ->assertJsonPath('success', true);

        $otpauthUrl = $response->json('data.otpauth_url');
        $this->assertStringStartsWith('otpauth://totp/', $otpauthUrl);
        $this->assertStringContainsString('secret=', $otpauthUrl);

        $fresh = $user->fresh();
        $this->assertFalse($fresh->totp_enabled);
        $this->assertNotNull($fresh->totp_secret);
    }

    public function test_enable_conflicts_when_already_enabled(): void
    {
        $user = UserModel::factory()->create();
        $this->enableTotp($user);

        $this->actingAs($user)->postJson('/profile/2fa/enable')->assertConflict();
    }

    public function test_confirm_with_valid_code_enables_totp_and_returns_8_recovery_codes(): void
    {
        $user = UserModel::factory()->create();
        $this->actingAs($user)->postJson('/profile/2fa/enable');

        $code = app(TotpService::class)->generateCode($user->fresh()->totp_secret);

        $response = $this->actingAs($user)->postJson('/profile/2fa/confirm', ['code' => $code]);

        $response->assertOk()->assertJsonPath('success', true);

        $recoveryCodes = $response->json('data.recovery_codes');
        $this->assertCount(8, $recoveryCodes);

        $fresh = $user->fresh();
        $this->assertTrue($fresh->totp_enabled);

        // В ответе — открытые коды, в БД — только их хеши.
        $storedHashes = $fresh->totp_recovery_codes;
        $this->assertCount(8, $storedHashes);
        foreach ($recoveryCodes as $plain) {
            $this->assertNotContains($plain, $storedHashes);
            $this->assertContains(app(TotpService::class)->hashRecoveryCode($plain), $storedHashes);
        }
    }

    public function test_confirm_with_invalid_code_returns_422_and_keeps_flag_off(): void
    {
        $user = UserModel::factory()->create();
        $this->actingAs($user)->postJson('/profile/2fa/enable');

        $response = $this->actingAs($user)->postJson('/profile/2fa/confirm', ['code' => '000000']);

        $response->assertUnprocessable()->assertJsonValidationErrors('code');
        $this->assertFalse($user->fresh()->totp_enabled);
    }

    public function test_confirm_without_pending_secret_returns_422(): void
    {
        $user = UserModel::factory()->create();

        $this->actingAs($user)->postJson('/profile/2fa/confirm', ['code' => '123456'])
            ->assertUnprocessable();
    }

    public function test_disable_with_wrong_password_returns_422_and_keeps_totp(): void
    {
        $user = UserModel::factory()->create();
        $this->enableTotp($user);

        $response = $this->actingAs($user)->postJson('/profile/2fa/disable', [
            'current_password' => 'wrong-password',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors('current_password');

        $fresh = $user->fresh();
        $this->assertTrue($fresh->totp_enabled);
        $this->assertNotNull($fresh->totp_secret);
        $this->assertNotNull($fresh->totp_recovery_codes);
    }

    public function test_disable_with_valid_password_clears_secret_and_recovery_codes(): void
    {
        $user = UserModel::factory()->create();
        $this->enableTotp($user);

        $response = $this->actingAs($user)->postJson('/profile/2fa/disable', [
            'current_password' => 'password',
        ]);

        $response->assertOk()->assertJsonPath('success', true);

        $fresh = $user->fresh();
        $this->assertFalse($fresh->totp_enabled);
        $this->assertNull($fresh->totp_secret);
        $this->assertNull($fresh->totp_recovery_codes);
    }

    public function test_disable_conflicts_when_not_enabled(): void
    {
        $user = UserModel::factory()->create();

        $this->actingAs($user)->postJson('/profile/2fa/disable', [
            'current_password' => 'password',
        ])->assertConflict();
    }

    /**
     * Включает 2FA пользователю через публичный контур enable + confirm.
     */
    private function enableTotp(UserModel $user): void
    {
        $this->actingAs($user)->postJson('/profile/2fa/enable');
        $code = app(TotpService::class)->generateCode($user->fresh()->totp_secret);
        $this->actingAs($user)->postJson('/profile/2fa/confirm', ['code' => $code]);
    }
}
