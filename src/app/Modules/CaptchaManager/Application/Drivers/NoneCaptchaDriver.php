<?php

namespace App\Modules\CaptchaManager\Application\Drivers;

use App\Modules\CaptchaManager\Domain\CaptchaDriverInterface;

/**
 * Null-object для выключенной капчи: проверка всегда успешна,
 * виджет на фронте не рендерится.
 */
class NoneCaptchaDriver implements CaptchaDriverInterface
{
    public function getDriverName(): string
    {
        return 'none';
    }

    public function getSiteKey(): string
    {
        return '';
    }

    public function verify(string $token, ?string $ip): bool
    {
        return true;
    }

    public function getWidgetConfig(): array
    {
        return [
            'driver' => $this->getDriverName(),
            'site_key' => $this->getSiteKey(),
        ];
    }
}
