<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\UserManager\Application\Services;

use App\Modules\UserManager\Application\Services\TotpService;
use Tests\TestCase;

class TotpServiceTest extends TestCase
{
    private TotpService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TotpService;
    }

    public function test_generated_secret_is_32_char_base32_and_produces_6_digit_code(): void
    {
        $secret = $this->service->generateSecret();

        $this->assertMatchesRegularExpression('/^[A-Z2-7]{32}$/', $secret);
        $this->assertMatchesRegularExpression('/^\d{6}$/', $this->service->generateCode($secret, 1_700_000_000));
    }

    public function test_code_is_valid_for_current_and_adjacent_windows(): void
    {
        $secret = $this->service->generateSecret();
        $base = 1_700_000_000; // 20 с внутри 30-секундного окна (не граница)

        $this->assertTrue($this->service->verify($secret, $this->service->generateCode($secret, $base), $base));
        $this->assertTrue($this->service->verify($secret, $this->service->generateCode($secret, $base - 30), $base));
        $this->assertTrue($this->service->verify($secret, $this->service->generateCode($secret, $base + 30), $base));
    }

    public function test_code_is_invalid_two_windows_away(): void
    {
        $secret = $this->service->generateSecret();
        $base = 1_700_000_000;

        $this->assertFalse($this->service->verify($secret, $this->service->generateCode($secret, $base - 60), $base));
        $this->assertFalse($this->service->verify($secret, $this->service->generateCode($secret, $base + 60), $base));
        $this->assertFalse($this->service->verify($secret, '000000', $base + 15));
    }

    public function test_otpauth_url_contains_label_secret_and_standard_parameters(): void
    {
        $url = $this->service->buildOtpAuthUrl('JBSWY3DPEHPK3PXP', 'user@example.com', 'Тестовый сайт');

        $this->assertStringStartsWith('otpauth://totp/', $url);
        $this->assertStringContainsString('secret=JBSWY3DPEHPK3PXP', $url);
        $this->assertStringContainsString('algorithm=SHA1', $url);
        $this->assertStringContainsString('digits=6', $url);
        $this->assertStringContainsString('period=30', $url);
        $this->assertStringContainsString(rawurlencode('Тестовый сайт').':'.rawurlencode('user@example.com'), $url);
    }

    public function test_recovery_codes_are_8_unique_64bit_codes_in_readable_format(): void
    {
        $codes = $this->service->generateRecoveryCodes();

        $this->assertCount(8, $codes);
        $this->assertCount(8, array_unique($codes));

        foreach ($codes as $code) {
            // 64 бита энтропии: 16 hex-символов в читаемом формате XXXX-XXXX-XXXX-XXXX
            $this->assertMatchesRegularExpression('/^[0-9A-F]{4}(-[0-9A-F]{4}){3}$/', $code);
            $this->assertSame(16, strlen(str_replace('-', '', $code)));
        }
    }

    public function test_generated_recovery_codes_pass_hash_match_and_burn(): void
    {
        $codes = $this->service->generateRecoveryCodes();
        $hashes = array_map(fn (string $code) => $this->service->hashRecoveryCode($code), $codes);

        foreach ($codes as $code) {
            $this->assertTrue($this->service->recoveryCodeMatches($code, $hashes));
        }

        $remaining = $this->service->burnRecoveryCode($codes[0], $hashes);

        $this->assertNotNull($remaining);
        $this->assertCount(7, $remaining);
        $this->assertFalse($this->service->recoveryCodeMatches($codes[0], $remaining));
    }

    public function test_recovery_code_matches_by_hash_regardless_of_case_and_dashes(): void
    {
        $codes = $this->service->generateRecoveryCodes();
        $hashes = array_map(fn (string $code) => $this->service->hashRecoveryCode($code), $codes);

        $this->assertTrue($this->service->recoveryCodeMatches(strtolower($codes[0]), $hashes));
        $this->assertTrue($this->service->recoveryCodeMatches(str_replace('-', '', $codes[0]), $hashes));
        $this->assertFalse($this->service->recoveryCodeMatches('FFFF-FFFF', $hashes));
    }

    public function test_recovery_code_burns_and_cannot_be_used_twice(): void
    {
        $codes = $this->service->generateRecoveryCodes();
        $hashes = array_map(fn (string $code) => $this->service->hashRecoveryCode($code), $codes);

        $remaining = $this->service->burnRecoveryCode($codes[0], $hashes);

        $this->assertNotNull($remaining);
        $this->assertCount(7, $remaining);
        $this->assertFalse($this->service->recoveryCodeMatches($codes[0], $remaining));
        $this->assertNull($this->service->burnRecoveryCode($codes[0], $remaining));

        // Остальные коды не пострадали.
        $this->assertTrue($this->service->recoveryCodeMatches($codes[1], $remaining));
    }
}
