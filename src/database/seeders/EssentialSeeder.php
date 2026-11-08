<?php

namespace Database\Seeders;

use App\Modules\SettingManager\Application\Services\SettingService;
use App\Modules\UserManager\Infrastructure\Models\GroupModel;
use App\Modules\UserManager\Infrastructure\Models\PermissionModel;
use App\Modules\UserManager\Infrastructure\Models\UserModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Essential-сеeding — минимум для работы prod-установки: группы, права,
 * администратор, дефолтные настройки. Запускается DatabaseSeeder по умолчанию
 * и из gavr:install. Демо-контент (материалы, галереи, меню, демо-пользователь)
 * — в DemoSeeder, в проде НЕ запускается.
 *
 * Пароль администратора никогда не хардкодится: приоритет — явный аргумент
 * (из gavr:install), затем ADMIN_PASSWORD из .env; если пусто — генерируется
 * случайный и ОДИН раз печатается в консоль. При запуске без консоли
 * (php artisan db:seed) сгенерированный пароль никому не показывается —
 * задайте ADMIN_PASSWORD в .env до сидирования.
 *
 * Идемпотентен: всё создаётся через firstOrCreate, существующие записи
 * (в т.ч. с изменённым вручную паролем) не перезаписываются.
 */
class EssentialSeeder extends Seeder
{
    public function __construct(
        private readonly ?string $adminEmail = null,
        private readonly ?string $adminName = null,
        private readonly ?string $adminPassword = null,
    ) {}

    public function run(): void
    {
        // Группы (essential для prod-установки)
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

        // Права доступа (essential для prod-установки)
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

        // Назначаем права группам
        $adminGroup->permissions()->sync(PermissionModel::all()->pluck('id')->toArray());

        $managerPermissions = PermissionModel::whereNotIn('key', ['users.manage', 'groups.manage', 'permissions.manage'])->pluck('id')->toArray();
        $managerGroup->permissions()->sync($managerPermissions);

        // Администратор — единственный пользователь Essential-сеedingа
        $email = $this->adminEmail ?: env('ADMIN_EMAIL') ?: 'admin@example.com';
        $name = $this->adminName ?: env('ADMIN_NAME') ?: 'Administrator';

        $password = $this->adminPassword ?: env('ADMIN_PASSWORD');
        $generated = $password === null || $password === '';

        if ($generated) {
            $password = Str::random(16);
        }

        $admin = UserModel::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'username' => 'admin',
                'password' => Hash::make($password),
                'blocked' => false,
                'activated' => true,
            ]
        );

        if ($admin->wasRecentlyCreated) {
            $admin->groups()->syncWithoutDetaching([$adminGroup->id]);

            if ($generated) {
                $this->command?->info('Admin password: '.$password);
                $this->command?->warn('Сохраните пароль — он больше нигде не показывается. Смените его после первого входа.');
            }
        }

        // Дефолтные настройки (essential: site_name, лимиты, SEO-поля).
        // homepage_type миграции гарантируют сами (insertOrIgnore).
        app(SettingService::class)->initializeDefaultSettings();
    }
}
