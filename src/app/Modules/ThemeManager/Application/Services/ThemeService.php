<?php

namespace App\Modules\ThemeManager\Application\Services;

use App\Modules\SettingManager\Infrastructure\Models\Setting;
use Illuminate\Database\QueryException;

class ThemeService
{
    /**
     * Get all available themes
     */
    public function getAvailableThemes(): array
    {
        return [
            [
                'id' => 'default',
                'name' => 'Светлая',
                'description' => 'Стандартная светлая тема с материалами',
                'colors' => [
                    'primary' => '#4f46e5',
                    'secondary' => '#7c3aed',
                    'bg' => '#f9fafb',
                    'surface' => '#ffffff',
                ],
            ],
            [
                'id' => 'warm',
                'name' => 'Тёплая',
                'description' => 'Уютная тёплая тема с материалами',
                'colors' => [
                    'primary' => '#d97706',
                    'secondary' => '#ea580c',
                    'bg' => '#fef3c7',
                    'surface' => '#fffbeb',
                ],
            ],
            [
                'id' => 'landing',
                'name' => 'Лендинг',
                'description' => 'Одностраничный сайт (лендинг), материалы скрыты',
                'colors' => [
                    'primary' => '#f97316',
                    'secondary' => '#dc2626',
                    'bg' => '#faf9fe',
                    'surface' => '#ffffff',
                ],
            ],
        ];
    }

    /**
     * Get current active theme
     */
    public function getCurrentTheme(): string
    {
        return Setting::where('key', 'theme')->first()?->value ?? 'default';
    }

    /**
     * Set current theme
     */
    public function setCurrentTheme(string $theme): void
    {
        // Паттерн SettingService::updateSettings: find → update, lazy-create
        // с догоном значением при гонке на unique settings.key (иначе
        // одновременное переключение темы давало бы 500 вместо сохранения).
        $setting = Setting::where('key', 'theme')->first();

        if ($setting) {
            $setting->update(['value' => $theme]);

            return;
        }

        try {
            Setting::create(['key' => 'theme', 'value' => $theme]);
        } catch (QueryException $e) {
            // Ключ успел появиться между first() и create() — догоняем значением.
            if (! $this->isUniqueViolation($e)) {
                throw $e;
            }

            Setting::where('key', 'theme')->update(['value' => $theme]);
        }
    }

    /**
     * Check if current theme is landing
     */
    public function isLandingTheme(): bool
    {
        return $this->getCurrentTheme() === 'landing';
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

