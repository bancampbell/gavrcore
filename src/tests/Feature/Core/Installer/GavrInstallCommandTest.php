<?php

declare(strict_types=1);

namespace Tests\Feature\Core\Installer;

use App\Modules\UserManager\Infrastructure\Models\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class GavrInstallCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        foreach (['ADMIN_EMAIL', 'ADMIN_NAME', 'ADMIN_PASSWORD', 'MANAGER_PASSWORD'] as $key) {
            unset($_ENV[$key], $_SERVER[$key]);
            putenv($key);
        }

        parent::tearDown();
    }

    public function test_installs_admin_non_interactively(): void
    {
        $this->artisan('gavr:install', [
            'email' => 'boss@example.com',
            '--name' => 'Boss',
            '--password' => 'Install-pass#1',
            '--force' => true,
        ])->assertExitCode(0);

        $admin = UserModel::where('email', 'boss@example.com')->sole();

        $this->assertSame('Boss', $admin->name);
        $this->assertTrue(Hash::check('Install-pass#1', $admin->password));
        $this->assertTrue($admin->groups()->where('alias', 'administrators')->exists());
        $this->assertDatabaseHas('settings', ['key' => 'site_name', 'value' => 'GavrCore CMS']);
    }

    public function test_second_run_reports_already_installed_without_duplicate(): void
    {
        $params = [
            'email' => 'boss@example.com',
            '--password' => 'Install-pass#1',
            '--force' => true,
        ];

        $this->artisan('gavr:install', $params)->assertExitCode(0);

        $this->artisan('gavr:install', $params)
            ->expectsOutputToContain('Уже установлено')
            ->assertExitCode(0);

        $this->assertDatabaseCount('users', 1);
    }

    public function test_refuses_install_when_another_admin_exists(): void
    {
        $this->artisan('gavr:install', [
            'email' => 'first@example.com',
            '--password' => 'Install-pass#1',
            '--force' => true,
        ])->assertExitCode(0);

        $this->artisan('gavr:install', [
            'email' => 'second@example.com',
            '--password' => 'Install-pass#2',
            '--force' => true,
        ])
            ->expectsOutputToContain('Уже установлено')
            ->assertExitCode(0);

        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseMissing('users', ['email' => 'second@example.com']);
    }

    public function test_demo_flag_seeds_demo_content(): void
    {
        $this->artisan('gavr:install', [
            'email' => 'boss@example.com',
            '--password' => 'Install-pass#1',
            '--demo' => true,
            '--force' => true,
        ])->assertExitCode(0);

        // Essential: admin; Demo: manager + контент
        $this->assertDatabaseCount('users', 2);
        $this->assertDatabaseHas('users', ['email' => 'manager@example.com']);
        $this->assertDatabaseCount('categories', 6);
        $this->assertDatabaseCount('materials', 19);
        $this->assertDatabaseCount('forms', 2);
        $this->assertDatabaseCount('form_submissions', 4);
        $this->assertDatabaseCount('galleries', 3);
    }
}
