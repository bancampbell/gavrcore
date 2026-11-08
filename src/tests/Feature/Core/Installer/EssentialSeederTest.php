<?php

declare(strict_types=1);

namespace Tests\Feature\Core\Installer;

use App\Modules\UserManager\Infrastructure\Models\GroupModel;
use App\Modules\UserManager\Infrastructure\Models\UserModel;
use Database\Seeders\EssentialSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EssentialSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        $this->unsetAdminEnv();

        parent::tearDown();
    }

    public function test_creates_exactly_one_admin_with_groups_and_permissions(): void
    {
        $this->seed(EssentialSeeder::class);

        // Ровно один пользователь — администратор (демо-пользователей нет)
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseHas('users', ['email' => 'admin@example.com', 'username' => 'admin']);
        $this->assertDatabaseMissing('users', ['email' => 'manager@example.com']);

        foreach (['administrators', 'managers', 'users'] as $alias) {
            $this->assertDatabaseHas('groups', ['alias' => $alias]);
        }

        $this->assertDatabaseCount('permissions', 8);

        $adminGroup = GroupModel::where('alias', 'administrators')->sole();
        $this->assertCount(8, $adminGroup->permissions);
        $this->assertTrue($adminGroup->users()->where('email', 'admin@example.com')->exists());

        // managers получают контент-права без users/groups/permissions.manage
        $managerGroup = GroupModel::where('alias', 'managers')->sole();
        $this->assertCount(5, $managerGroup->permissions);
        $this->assertFalse($managerGroup->users()->exists());

        // Дефолтные настройки — часть Essential
        $this->assertDatabaseHas('settings', ['key' => 'site_name', 'value' => 'GavrCore CMS']);
    }

    public function test_applies_password_from_admin_password_env(): void
    {
        $this->setAdminEnv(['ADMIN_PASSWORD' => 'Env-pass#123']);

        $this->seed(EssentialSeeder::class);

        $this->assertTrue(Hash::check('Env-pass#123', UserModel::sole()->password));
    }

    public function test_generates_and_prints_random_password_without_env(): void
    {
        $this->unsetAdminEnv();

        $exitCode = Artisan::call('db:seed', ['--class' => EssentialSeeder::class, '--force' => true]);

        $this->assertSame(0, $exitCode);

        $admin = UserModel::sole();

        // Старый захардкоженный дефолт из репозитория больше не применяется
        $this->assertFalse(Hash::check('admin123', $admin->password));

        // Сгенерированный пароль напечатан в консоль и реально подходит для входа
        $output = Artisan::output();
        $this->assertMatchesRegularExpression('/Admin password: (\S+)/', $output);

        preg_match('/Admin password: (\S+)/', $output, $matches);
        $this->assertTrue(Hash::check($matches[1], $admin->password));
    }

    public function test_is_idempotent_and_does_not_clobber_password(): void
    {
        $this->setAdminEnv(['ADMIN_PASSWORD' => 'First-pass#1']);

        $this->seed(EssentialSeeder::class);
        $this->seed(EssentialSeeder::class);

        $this->assertDatabaseCount('users', 1);
        $this->assertTrue(Hash::check('First-pass#1', UserModel::sole()->password));
    }

    private function setAdminEnv(array $vars): void
    {
        foreach ($vars as $key => $value) {
            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
        }
    }

    private function unsetAdminEnv(): void
    {
        foreach (['ADMIN_EMAIL', 'ADMIN_NAME', 'ADMIN_PASSWORD', 'MANAGER_PASSWORD'] as $key) {
            unset($_ENV[$key], $_SERVER[$key]);
            putenv($key);
        }
    }
}
