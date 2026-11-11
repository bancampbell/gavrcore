<?php

namespace App\Modules\CaptchaManager\Application\Drivers;

use App\Modules\CaptchaManager\Domain\CaptchaDriverInterface;
use App\Modules\SettingManager\Infrastructure\Models\Setting;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class TurnstileCaptchaDriver implements CaptchaDriverInterface
{
    private const VERIFY_URL = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';

    private const TIMEOUT = 5;

    public function getDriverName(): string
    {
        return 'turnstile';
    }

    public function getSiteKey(): string
    {
        return (string) Setting::get('captcha_site_key', '');
    }

    public function verify(string $token, ?string $ip): bool
    {
        $secret = (string) Setting::get('captcha_secret', '');

        if ($token === '' || $secret === '') {
            return false;
        }

        $payload = [
            'secret' => $secret,
            'response' => $token,
        ];

        if ($ip !== null) {
            $payload['remoteip'] = $ip;
        }

        try {
            $response = Http::asForm()->timeout(self::TIMEOUT)->post(self::VERIFY_URL, $payload);
        } catch (ConnectionException) {
            // Таймаут/недоступность Cloudflare — fail-closed.
            return false;
        }

        return $response->successful() && $response->json('success') === true;
    }

    public function getWidgetConfig(): array
    {
        return [
            'driver' => $this->getDriverName(),
            'site_key' => $this->getSiteKey(),
        ];
    }
}
