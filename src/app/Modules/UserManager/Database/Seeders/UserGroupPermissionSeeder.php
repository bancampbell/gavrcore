<?php

namespace App\Modules\UserManager\Database\Seeders;

use App\Modules\UserManager\Infrastructure\Models\GroupModel;
use App\Modules\UserManager\Infrastructure\Models\PermissionModel;
use App\Modules\UserManager\Infrastructure\Models\UserModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserGroupPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Создаём группы
        $adminGroup = GroupModel::firstOrCreate(
            ['alias' => 'administrators'],
            [
                'name' => 'Administrators',
                'description' => 'Full system access',
                'status' => true,
                'ordering' => 1,
            ]
        );

        $managerGroup = GroupModel::firstOrCreate(
            ['alias' => 'managers'],
            [
                'name' => 'Managers',
                'description' => 'Content management access',
                'status' => true,
                'ordering' => 2,
            ]
        );

        $userGroup = GroupModel::firstOrCreate(
            ['alias' => 'users'],
            [
                'name' => 'Users',
                'description' => 'Regular users',
                'status' => true,
                'ordering' => 3,
            ]
        );

        // Создаём права доступа
        $permissions = [
            ['name' => 'Доступ к админке', 'key' => 'admin.access', 'group' => 'system'],
            ['name' => 'Управление пользователями', 'key' => 'users.manage', 'group' => 'users'],
            ['name' => 'Управление группами', 'key' => 'groups.manage', 'group' => 'users'],
            ['name' => 'Управление правами', 'key' => 'permissions.manage', 'group' => 'users'],
            ['name' => 'Управление материалами', 'key' => 'materials.manage', 'group' => 'content'],
            ['name' => 'Управление категориями', 'key' => 'categories.manage', 'group' => 'content'],
            ['name' => 'Управление меню', 'key' => 'menu.manage', 'group' => 'content'],
            ['name' => 'Управление медиа', 'key' => 'media.manage', 'group' => 'content'],
        ];

        foreach ($permissions as $perm) {
            PermissionModel::firstOrCreate(
                ['key' => $perm['key']],
                $perm
            );
        }

        // Назначаем права
        $adminGroup->permissions()->sync(PermissionModel::all()->pluck('id')->toArray());

        $managerPermissions = PermissionModel::whereNotIn('key', ['users.manage', 'groups.manage', 'permissions.manage'])->pluck('id')->toArray();
        $managerGroup->permissions()->sync($managerPermissions);

        // Пароли из окружения; fallback на известные значения — только для local/testing.
        // На проде без ADMIN_PASSWORD/MANAGER_PASSWORD пароль будет случайным: учётка
        // создастся, но войти с известным паролем нельзя.
        $adminPassword = env('ADMIN_PASSWORD')
            ?: (app()->environment(['local', 'testing']) ? 'admin123' : Str::random(32));
        $managerPassword = env('MANAGER_PASSWORD')
            ?: (app()->environment(['local', 'testing']) ? 'manager123' : Str::random(32));

        // Создаём пользователя admin
        $admin = UserModel::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrator',
                'username' => 'admin',
                'password' => Hash::make($adminPassword),
                'blocked' => false,
                'activated' => true,
            ]
        );
        $admin->groups()->syncWithoutDetaching([$adminGroup->id]);

        // Создаём пользователя manager
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
