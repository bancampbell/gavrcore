<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\ThemeManager\Application\Services;

use App\Modules\SettingManager\Infrastructure\Models\Setting;
use App\Modules\ThemeManager\Application\Services\ThemeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Переключение темы (TECHDEBT п.4): setCurrentTheme переведён с голого
 * updateOrCreate на паттерн SettingService (find → update, lazy-create
 * с догоном значением при гонке на unique settings.key).
 */
class ThemeServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_set_current_theme_creates_setting_when_missing(): void
    {
        $this->assertDatabaseMissing('settings', ['key' => 'theme']);

        app(ThemeService::class)->setCurrentTheme('warm');

        $this->assertDatabaseHas('settings', ['key' => 'theme', 'value' => 'warm']);
        $this->assertSame('warm', app(ThemeService::class)->getCurrentTheme());
    }

    public function test_set_current_theme_updates_existing_setting(): void
    {
        Setting::create(['key' => 'theme', 'value' => 'default']);

        app(ThemeService::class)->setCurrentTheme('landing');

        $this->assertDatabaseHas('settings', ['key' => 'theme', 'value' => 'landing']);
        $this->assertSame('landing', app(ThemeService::class)->getCurrentTheme());
    }
}
