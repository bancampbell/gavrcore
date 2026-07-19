<?php

namespace App\Services;

use App\Models\Setting;

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
                ]
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
                ]
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
                ]
            ]
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
        Setting::updateOrCreate(
            ['key' => 'theme'],
            ['value' => $theme]
        );
    }

    /**
     * Check if current theme is landing
     */
    public function isLandingTheme(): bool
    {
        return $this->getCurrentTheme() === 'landing';
    }
}
