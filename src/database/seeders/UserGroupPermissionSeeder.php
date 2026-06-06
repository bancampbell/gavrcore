<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Group;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserGroupPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Создаём группы
        $adminGroup = Group::firstOrCreate(
            ['alias' => 'administrators'],
            [
                'name' => 'Administrators',
                'description' => 'Full system access',
                'status' => true,
                'ordering' => 1,
            ]
        );

        $managerGroup = Group::firstOrCreate(
            ['alias' => 'managers'],
            [
                'name' => 'Managers',
                'description' => 'Content management access',
                'status' => true,
                'ordering' => 2,
            ]
        );

        $userGroup = Group::firstOrCreate(
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
            Permission::firstOrCreate(
                ['key' => $perm['key']],
                $perm
            );
        }

        // Назначаем права
        $adminGroup->permissions()->sync(Permission::all()->pluck('id')->toArray());

        $managerPermissions = Permission::whereNotIn('key', ['users.manage', 'groups.manage', 'permissions.manage'])->pluck('id')->toArray();
        $managerGroup->permissions()->sync($managerPermissions);

        // Создаём пользователя admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrator',
                'username' => 'admin',
                'password' => Hash::make('admin123'),
                'blocked' => false,
                'activated' => true,
            ]
        );
        $admin->groups()->syncWithoutDetaching([$adminGroup->id]);

        // Создаём пользователя manager
        $manager = User::firstOrCreate(
            ['email' => 'manager@example.com'],
            [
                'name' => 'Manager',
                'username' => 'manager',
                'password' => Hash::make('manager123'),
                'blocked' => false,
                'activated' => true,
            ]
        );
        $manager->groups()->syncWithoutDetaching([$managerGroup->id]);
    }
}
