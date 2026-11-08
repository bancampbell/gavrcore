<?php

namespace App\Modules\UserManager\Database\Seeders;

use App\Modules\UserManager\Infrastructure\Models\GroupModel;
use App\Modules\UserManager\Infrastructure\Models\UserModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Демо-пользователь Manager — ТОЛЬКО для разработки/демо, в проде не нужен.
 * Запускается из DemoSeeder (php artisan db:seed --class=DemoSeeder).
 *
 * Пароль: MANAGER_PASSWORD из .env; на local/testing fallback 'manager123'
 * (демо-учётка, не прод). Без MANAGER_PASSWORD в остальных окружениях —
 * случайный: войти не получится, что для демо-пользователя допустимо.
 *
 * Группа managers создаётся и наполняется правами в EssentialSeeder; здесь
 * firstOrCreate — защита от запуска DemoSeeder без Essential (тогда группа
 * будет без прав — это демо-путь, прод-установка идёт через Essential).
 */
class ManagerUserSeeder extends Seeder
{
    public function run(): void
    {
        $managerGroup = GroupModel::firstOrCreate(
            ['alias' => 'managers'],
            [
                'name' => 'Managers',
                'description' => 'Content management access',
                'status' => true,
                'ordering' => 2,
            ]
        );

        $managerPassword = env('MANAGER_PASSWORD')
            ?: (app()->environment(['local', 'testing']) ? 'manager123' : Str::random(32));

        $manager = UserModel::firstOrCreate(
            ['email' => 'manager@example.com'],
            [
                'name' => 'Manager',
                'username' => 'manager',
                'password' => Hash::make($managerPassword),
                'blocked' => false,
                'activated' => true,
            ]
        );
        $manager->groups()->syncWithoutDetaching([$managerGroup->id]);
    }
}
