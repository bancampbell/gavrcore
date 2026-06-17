<?php

namespace App\Services;

use App\Models\Setting;

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
            } elseif ($setting->type === 'number') {
                $value = is_numeric($value) ? (float) $value : 0;
            }

            $result[$setting->key] = $value;
        }

        return $result;
    }

    public function updateSettings(array $settings): void
    {
        foreach ($settings as $key => $value) {
            $setting = Setting::where('key', $key)->first();

            // Для boolean полей преобразуем в строку '1' или '0'
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

                Setting::create([
                    'key' => $key,
                    'value' => $value,
                    'type' => $type,
                    'group' => 'general',
                    'label' => $key,
                    'order' => 0,
                ]);
            }
        }
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
        ];

        foreach ($defaults as $default) {
            Setting::firstOrCreate(
                ['key' => $default['key']],
                $default
            );
        }
    }
}
