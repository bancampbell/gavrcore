<?php

namespace App\Modules\SettingManager\Application\Services;

use App\Modules\SettingManager\Infrastructure\Models\Setting;
use Illuminate\Database\QueryException;

class SettingService
{
    public function getAllSettings(): array
    {
        $settings = Setting::all();
        $result = [];

        foreach ($settings as $setting) {
            $value = $setting->value;

            if ($setting->type === 'boolean') {
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN);
            } elseif ($setting->type === 'number' && is_numeric($value)) {
                // Нечисловое значение (например, тестовый ключ капчи '1x0000...AA')
                // отдаём как есть — иначе админка/фронт получали 0 вместо ключа.
                $value = (float) $value;
            }

            $result[$setting->key] = $value;
        }

        return $result;
    }

    public function updateSettings(array $settings): void
    {
        foreach ($settings as $key => $value) {
            $setting = Setting::where('key', $key)->first();

            if (is_bool($value)) {
                $value = $value ? '1' : '0';
            }

            if ($setting) {
                $setting->update(['value' => $value]);
            } else {
                $type = 'string';
                if (is_bool($value) || $value === '1' || $value === '0') {
                    $type = 'boolean';
                } elseif (is_numeric($value)) {
                    $type = 'number';
                }

                try {
                    Setting::create([
                        'key' => $key,
                        'value' => $value,
                        'type' => $type,
                        'group' => 'general',
                        'label' => $key,
                        'order' => 0,
                    ]);
                } catch (QueryException $e) {
                    // Ключ успел появиться между first() и create() — догоняем значением.
                    // Ловим только unique-нарушение, всё остальное пробрасываем.
                    if (! $this->isUniqueViolation($e)) {
                        throw $e;
                    }

                    Setting::where('key', $key)->update(['value' => $value]);
                }
            }
        }
    }

    public function getTheme(): string
    {
        return Setting::where('key', 'theme')->first()?->value ?? 'default';
    }

    public function initializeDefaultSettings(): void
    {
        $defaults = [
            ['key' => 'site_name', 'value' => 'GavrCore CMS', 'type' => 'string', 'group' => 'general', 'label' => 'Название сайта', 'order' => 1],
            ['key' => 'site_description', 'value' => '', 'type' => 'text', 'group' => 'general', 'label' => 'Описание сайта', 'order' => 2],
            ['key' => 'admin_email', 'value' => 'admin@example.com', 'type' => 'string', 'group' => 'general', 'label' => 'Email администратора', 'order' => 3],
            ['key' => 'seo_keywords', 'value' => '', 'type' => 'string', 'group' => 'seo', 'label' => 'Ключевые слова', 'order' => 1],
            ['key' => 'materials_per_page', 'value' => '10', 'type' => 'number', 'group' => 'materials', 'label' => 'Материалов на странице', 'order' => 1],
            ['key' => 'date_format', 'value' => 'd.m.Y', 'type' => 'string', 'group' => 'materials', 'label' => 'Формат даты', 'order' => 2],
            ['key' => 'show_date', 'value' => '1', 'type' => 'boolean', 'group' => 'materials', 'label' => 'Показывать дату', 'order' => 3],
            ['key' => 'show_author', 'value' => '1', 'type' => 'boolean', 'group' => 'materials', 'label' => 'Показывать автора', 'order' => 4],
            ['key' => 'show_category', 'value' => '1', 'type' => 'boolean', 'group' => 'materials', 'label' => 'Показывать категорию', 'order' => 5],
            ['key' => 'show_views', 'value' => '1', 'type' => 'boolean', 'group' => 'materials', 'label' => 'Показывать просмотры', 'order' => 6],
            ['key' => 'max_file_size', 'value' => '5', 'type' => 'number', 'group' => 'media', 'label' => 'Макс. размер файла (MB)', 'order' => 1],
            ['key' => 'allowed_formats', 'value' => 'jpg,jpeg,png,gif,webp', 'type' => 'string', 'group' => 'media', 'label' => 'Разрешенные форматы', 'order' => 2],
            ['key' => 'image_quality', 'value' => '80', 'type' => 'number', 'group' => 'media', 'label' => 'Качество изображений (%)', 'order' => 3],
            ['key' => 'debug_mode', 'value' => '0', 'type' => 'boolean', 'group' => 'system', 'label' => 'Режим отладки', 'order' => 1],
            ['key' => 'maintenance_mode', 'value' => '0', 'type' => 'boolean', 'group' => 'system', 'label' => 'Сайт на обслуживании', 'order' => 2],
            ['key' => 'timezone', 'value' => 'UTC', 'type' => 'string', 'group' => 'system', 'label' => 'Часовой пояс', 'order' => 3],
            ['key' => 'robots', 'value' => 'index,follow', 'type' => 'string', 'group' => 'seo', 'label' => 'Robots', 'order' => 2],
            ['key' => 'google_analytics_id', 'value' => '', 'type' => 'string', 'group' => 'seo', 'label' => 'Google Analytics ID', 'order' => 3],
            ['key' => 'yandex_metrika_id', 'value' => '', 'type' => 'string', 'group' => 'seo', 'label' => 'Яндекс Метрика ID', 'order' => 4],
            ['key' => 'cache_enabled', 'value' => '0', 'type' => 'boolean', 'group' => 'cache', 'label' => 'Кэшировать страницы', 'order' => 1],
            ['key' => 'cache_ttl', 'value' => '60', 'type' => 'number', 'group' => 'cache', 'label' => 'Время кэша (минуты)', 'order' => 2],
            ['key' => 'captcha_driver', 'value' => 'turnstile', 'type' => 'string', 'group' => 'captcha', 'label' => 'Драйвер капчи', 'order' => 1],
            ['key' => 'captcha_site_key', 'value' => '1x00000000000000000000AA', 'type' => 'string', 'group' => 'captcha', 'label' => 'Site Key капчи', 'order' => 2],
            ['key' => 'captcha_secret', 'value' => '1x0000000000000000000000000000000AA', 'type' => 'string', 'group' => 'captcha', 'label' => 'Secret Key капчи', 'order' => 3],
        ];

        foreach ($defaults as $default) {
            Setting::firstOrCreate(
                ['key' => $default['key']],
                $default
            );
        }
    }

    private function isUniqueViolation(QueryException $e): bool
    {
        // MySQL: 1062, PostgreSQL: 23505, SQLite: 8/19/1555/2067 (SQLITE_CONSTRAINT*).
        // Драйвер pgsql кладёт в errorInfo[1] свой код (7), а не SQLSTATE —
        // 23505 ищем в errorInfo[0]/getCode() (эталон — UserManager).
        $codes = array_filter([
            (string) ($e->errorInfo[0] ?? ''),
            (string) $e->getCode(),
        ]);

        return count(array_intersect($codes, ['1062', '23505', '8', '19', '1555', '2067'])) > 0;
    }
}
