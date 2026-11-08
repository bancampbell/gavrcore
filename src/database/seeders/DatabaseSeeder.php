<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * По умолчанию сидируется ТОЛЬКО Essential-минимум (prod-установка):
     * группы, права, администратор, дефолтные настройки.
     *
     * Демо-контент (материалы, галереи, меню, демо-пользователь) —
     * опционально, отдельной командой:
     *
     *     php artisan db:seed --class=DemoSeeder
     *
     * (или сразу при установке: php artisan gavr:install --demo)
     */
    public function run(): void
    {
        $this->call([
            EssentialSeeder::class,
        ]);
    }
}
