<?php

namespace App\Modules\CaptchaManager\Application;

use App\Modules\CaptchaManager\Application\Drivers\NoneCaptchaDriver;
use App\Modules\CaptchaManager\Application\Drivers\TurnstileCaptchaDriver;
use App\Modules\CaptchaManager\Domain\CaptchaDriverInterface;
use App\Modules\SettingManager\Infrastructure\Models\Setting;

class CaptchaManager
{
    /**
     * Реестр драйверов: алиас => класс.
     * Новый драйвер = класс + строка в этом массиве.
     */
    private const DRIVERS = [
        'none' => NoneCaptchaDriver::class,
        'turnstile' => TurnstileCaptchaDriver::class,
    ];

    /**
     * Активный драйвер из настройки captcha_driver.
     * Неизвестный алиас/пустое значение/отсутствие настройки — none-драйвер.
     */
    public function driver(): CaptchaDriverInterface
    {
        $alias = (string) Setting::get('captcha_driver', 'none');

        $class = self::DRIVERS[$alias] ?? NoneCaptchaDriver::class;

        return app($class);
    }

    /**
     * Проверка токена активным драйвером.
     */
    public function verify(string $token, ?string $ip = null): bool
    {
        return $this->driver()->verify($token, $ip);
    }

    /**
     * Конфиг виджета активного драйвера для фронта.
     */
    public function getWidgetConfig(): array
    {
        return $this->driver()->getWidgetConfig();
    }
}
