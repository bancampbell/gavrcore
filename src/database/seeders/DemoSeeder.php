<?php

namespace Database\Seeders;

use App\Modules\FormBuilder\Database\Seeders\FormSubmissionsSeeder;
use App\Modules\FormBuilder\Database\Seeders\FormsSeeder;
use App\Modules\GalleryManager\Database\Seeders\GallerySeeder;
use App\Modules\UserManager\Database\Seeders\ManagerUserSeeder;
use Illuminate\Database\Seeder;

/**
 * Демо-контент — ТОЛЬКО для разработки/демо, в проде НЕ запускать.
 *
 * Запуск отдельно: php artisan db:seed --class=DemoSeeder
 * (обычно после gavr:install или DatabaseSeeder — т.е. EssentialSeeder
 * уже выполнен; SettingsSeeder ниже дополнительно назначает первый
 * опубликованный материал главной страницей).
 *
 * Идемпотентен: всё создаётся через firstOrCreate, повторный запуск
 * не дублирует записи.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ManagerUserSeeder::class,
            CategorySeeder::class,
            FormsSeeder::class,
            MaterialSeeder::class,
            GallerySeeder::class,
            SettingsSeeder::class,
            MenuSeeder::class,
            FormSubmissionsSeeder::class,
        ]);
    }
}
