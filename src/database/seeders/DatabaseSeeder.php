<?php

namespace Database\Seeders;

use App\Modules\FormBuilder\Database\Seeders\FormsSeeder;
use App\Modules\FormBuilder\Database\Seeders\FormSubmissionsSeeder;
use App\Modules\GalleryManager\Database\Seeders\GallerySeeder;
use App\Modules\UserManager\Database\Seeders\UserGroupPermissionSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserGroupPermissionSeeder::class,
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
