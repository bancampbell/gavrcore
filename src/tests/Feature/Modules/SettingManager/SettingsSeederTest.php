<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\SettingManager;

use Database\Seeders\SettingsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Modules\MaterialManager\Infrastructure\Models\MaterialModelFactory;
use Tests\TestCase;

/**
 * Главная назначается настройками (homepage_type/homepage_material_id),
 * а флаг show_on_homepage — производный индикатор. После сидирования
 * они должны быть согласованы: чекбокс «На главной» стоит у главного.
 */
class SettingsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_syncs_homepage_flag_with_settings(): void
    {
        $first = (new MaterialModelFactory)->published()->create();
        $second = (new MaterialModelFactory)->published()->create();

        $this->seed(SettingsSeeder::class);

        $this->assertDatabaseHas('settings', ['key' => 'homepage_type', 'value' => 'material']);
        $this->assertDatabaseHas('settings', [
            'key' => 'homepage_material_id',
            'value' => (string) $first->id,
        ]);
        $this->assertDatabaseHas('materials', ['id' => $first->id, 'show_on_homepage' => true]);
        $this->assertDatabaseHas('materials', ['id' => $second->id, 'show_on_homepage' => false]);
    }
}
