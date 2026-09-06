<?php

namespace Database\Seeders;

use App\Modules\UserManager\Database\Seeders\UserGroupPermissionSeeder;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CategorySeeder::class,
            MaterialSeeder::class,
            MenuSeeder::class,
            UserGroupPermissionSeeder::class,
        ]);
    }
}
