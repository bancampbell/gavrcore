<?php

namespace App\Modules\CaptchaManager\Domain;

interface CaptchaDriverInterface
{
    /**
     * Алиас драйвера, совпадает со значением настройки captcha_driver.
     */
    public function getDriverName(): string;

    /**
     * Публичный ключ виджета (для фронта). Пустая строка, если драйвер выключен.
     */
    public function getSiteKey(): string;

    /**
     * Проверка токена капчи на сервере провайдера.
     * Недоступность провайдера/таймаут — всегда false (fail-closed).
     */
    public function verify(string $token, ?string $ip): bool;

    /**
     * Конфиг виджета для фронта (driver, site_key и т.п.).
     */
    public function getWidgetConfig(): array;
}
