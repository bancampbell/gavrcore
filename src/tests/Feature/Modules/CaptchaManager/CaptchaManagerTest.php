<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\CaptchaManager;

use App\Modules\CaptchaManager\Application\CaptchaManager;
use App\Modules\CaptchaManager\Application\Drivers\NoneCaptchaDriver;
use App\Modules\CaptchaManager\Application\Drivers\TurnstileCaptchaDriver;
use App\Modules\CaptchaManager\Domain\CaptchaDriverInterface;
use App\Modules\SettingManager\Infrastructure\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CaptchaManagerTest extends TestCase
{
    use RefreshDatabase;

    public function test_turnstile_setting_resolves_turnstile_driver(): void
    {
        $this->setSetting('captcha_driver', 'turnstile');

        $this->assertInstanceOf(TurnstileCaptchaDriver::class, (new CaptchaManager)->driver());
    }

    public function test_none_setting_resolves_none_driver(): void
    {
        $this->setSetting('captcha_driver', 'none');

        $this->assertInstanceOf(NoneCaptchaDriver::class, (new CaptchaManager)->driver());
    }

    public function test_missing_empty_and_unknown_driver_fall_back_to_none(): void
    {
        // Настройки нет вообще
        $this->assertInstanceOf(NoneCaptchaDriver::class, (new CaptchaManager)->driver());

        $this->setSetting('captcha_driver', '');
        $this->assertInstanceOf(NoneCaptchaDriver::class, (new CaptchaManager)->driver());

        $this->setSetting('captcha_driver', 'unknown-vendor');
        $this->assertInstanceOf(NoneCaptchaDriver::class, (new CaptchaManager)->driver());
    }

    public function test_none_driver_verify_always_true(): void
    {
        $driver = new NoneCaptchaDriver;

        $this->assertTrue($driver->verify('any-token', null));
        $this->assertTrue($driver->verify('', '10.0.0.1'));
        $this->assertSame('none', $driver->getDriverName());
        $this->assertSame('', $driver->getSiteKey());
    }

    public function test_fake_driver_via_container_receives_verify_arguments(): void
    {
        $fake = new class implements CaptchaDriverInterface
        {
            public ?string $token = null;

            public ?string $ip = null;

            public function getDriverName(): string
            {
                return 'fake';
            }

            public function getSiteKey(): string
            {
                return 'fake-site-key';
            }

            public function verify(string $token, ?string $ip): bool
            {
                $this->token = $token;
                $this->ip = $ip;

                return true;
            }

            public function getWidgetConfig(): array
            {
                return ['driver' => $this->getDriverName(), 'site_key' => $this->getSiteKey()];
            }
        };

        $this->app->instance(TurnstileCaptchaDriver::class, $fake);
        $this->setSetting('captcha_driver', 'turnstile');

        $manager = new CaptchaManager;
        $this->assertSame($fake, $manager->driver());
        $this->assertTrue($manager->verify('token-123', '10.0.0.1'));
        $this->assertSame('token-123', $fake->token);
        $this->assertSame('10.0.0.1', $fake->ip);
    }

    public function test_turnstile_verify_sends_secret_token_and_ip(): void
    {
        Http::fake([
            'challenges.cloudflare.com/*' => Http::response(['success' => true]),
        ]);

        $this->setSetting('captcha_site_key', 'site-key-1');
        $this->setSetting('captcha_secret', 'secret-1');

        $driver = new TurnstileCaptchaDriver;
        $this->assertTrue($driver->verify('XXXX.DUMMY.TOKEN.1', '127.0.0.1'));

        Http::assertSent(function (Request $request) {
            return $request->url() === 'https://challenges.cloudflare.com/turnstile/v0/siteverify'
                && $request['secret'] === 'secret-1'
                && $request['response'] === 'XXXX.DUMMY.TOKEN.1'
                && $request['remoteip'] === '127.0.0.1';
        });
    }

    public function test_turnstile_verify_false_when_cloudflare_rejects_token(): void
    {
        Http::fake([
            'challenges.cloudflare.com/*' => Http::response(['success' => false, 'error-codes' => ['invalid-input-response']]),
        ]);

        $this->setSetting('captcha_secret', 'secret-1');

        $this->assertFalse((new TurnstileCaptchaDriver)->verify('bad-token', null));
    }

    public function test_turnstile_verify_false_on_http_error(): void
    {
        Http::fake([
            'challenges.cloudflare.com/*' => Http::response('', 500),
        ]);

        $this->setSetting('captcha_secret', 'secret-1');

        $this->assertFalse((new TurnstileCaptchaDriver)->verify('token', null));
    }

    public function test_turnstile_verify_false_on_timeout(): void
    {
        Http::fake([
            'challenges.cloudflare.com/*' => function () {
                throw new ConnectionException('Connection timed out');
            },
        ]);

        $this->setSetting('captcha_secret', 'secret-1');

        $this->assertFalse((new TurnstileCaptchaDriver)->verify('token', null));
    }

    public function test_turnstile_verify_false_without_request_when_token_or_secret_empty(): void
    {
        Http::fake();

        $this->setSetting('captcha_secret', '');

        $this->assertFalse((new TurnstileCaptchaDriver)->verify('token', null));

        $this->setSetting('captcha_secret', 'secret-1');

        $this->assertFalse((new TurnstileCaptchaDriver)->verify('', null));

        Http::assertNothingSent();
    }

    public function test_turnstile_widget_config_contains_driver_and_site_key(): void
    {
        $this->setSetting('captcha_site_key', 'site-key-1');

        $this->assertSame(
            ['driver' => 'turnstile', 'site_key' => 'site-key-1'],
            (new TurnstileCaptchaDriver)->getWidgetConfig()
        );
    }

    public function test_none_widget_config_has_empty_site_key(): void
    {
        $this->assertSame(
            ['driver' => 'none', 'site_key' => ''],
            (new NoneCaptchaDriver)->getWidgetConfig()
        );
    }

    public function test_manager_widget_config_comes_from_active_driver(): void
    {
        $this->setSetting('captcha_driver', 'turnstile');
        $this->setSetting('captcha_site_key', 'site-key-1');

        $this->assertSame(
            ['driver' => 'turnstile', 'site_key' => 'site-key-1'],
            (new CaptchaManager)->getWidgetConfig()
        );
    }

    private function setSetting(string $key, string $value): void
    {
        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => 'string', 'group' => 'captcha', 'label' => $key, 'order' => 0]
        );
    }
}
