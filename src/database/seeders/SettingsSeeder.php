<?php

namespace Database\Seeders;

use App\Modules\MaterialManager\Infrastructure\Models\MaterialModel;
use App\Modules\SettingManager\Application\Services\SettingService;
use App\Modules\SettingManager\Infrastructure\Models\Setting;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Инициализирует дефолтные настройки (AUDIT-12) и назначение главной
     * страницы (AUDIT-13). Идемпотентен: всё создаётся через firstOrCreate —
     * повторный seed не дублирует записи и не перезаписывает значения,
     * изменённые вручную через админку.
     */
    public function run(): void
    {
        app(SettingService::class)->initializeDefaultSettings();

        Setting::firstOrCreate(
            ['key' => 'homepage_type'],
            [
                'value' => 'material',
                'type' => 'string',
                'group' => 'general',
                'label' => 'Тип главной страницы',
                'order' => 4,
            ]
        );

        $homepageMaterial = MaterialModel::where('state', 'published')
            ->orderBy('id')
            ->first();

        if ($homepageMaterial !== null) {
            Setting::firstOrCreate(
                ['key' => 'homepage_material_id'],
                [
                    'value' => (string) $homepageMaterial->id,
                    'type' => 'number',
                    'group' => 'general',
                    'label' => 'Главный материал',
                    'order' => 5,
                ]
            );
        }
    }
}
