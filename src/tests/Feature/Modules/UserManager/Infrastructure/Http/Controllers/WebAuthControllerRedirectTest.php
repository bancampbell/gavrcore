<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\UserManager\Infrastructure\Http\Controllers;

use App\Modules\UserManager\Infrastructure\Models\UserModel as User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Tests\TestCase;

/**
 * TECHDEBT-52: реферер со страницы логина ведёт на главную, но матчинг
 * должен быть точным — '/admin/login', '/login/...' и чужие хосты
 * страницей логина не считаются (intended-дефолт '/dashboard').
 */
class WebAuthControllerRedirectTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ThrottleRequests::class);
    }

    public function test_login_from_login_page_redirects_home(): void
    {
        User::factory()->create(['email' => 'user@example.com']);

        $this->withHeader('referer', 'http://localhost/login')
            ->post('/login', [
                'email' => 'user@example.com',
                'password' => 'password',
            ])->assertRedirect('/');
    }

    public function test_login_from_login_page_with_trailing_slash_redirects_home(): void
    {
        User::factory()->create(['email' => 'user@example.com']);

        $this->withHeader('referer', 'http://localhost/login/')
            ->post('/login', [
                'email' => 'user@example.com',
                'password' => 'password',
            ])->assertRedirect('/');
    }

    public function test_login_from_admin_login_page_goes_to_intended_default(): void
    {
        User::factory()->create(['email' => 'user@example.com']);

        $this->withHeader('referer', 'http://localhost/admin/login')
            ->post('/login', [
                'email' => 'user@example.com',
                'password' => 'password',
            ])->assertRedirect('/dashboard');
    }

    public function test_login_from_login_subpath_goes_to_intended_default(): void
    {
        User::factory()->create(['email' => 'user@example.com']);

        $this->withHeader('referer', 'http://localhost/login/anything')
            ->post('/login', [
                'email' => 'user@example.com',
                'password' => 'password',
            ])->assertRedirect('/dashboard');
    }

    public function test_login_with_foreign_host_login_referer_goes_to_intended_default(): void
    {
        User::factory()->create(['email' => 'user@example.com']);

        $this->withHeader('referer', 'http://evil.example.com/login')
            ->post('/login', [
                'email' => 'user@example.com',
                'password' => 'password',
            ])->assertRedirect('/dashboard');
    }

    public function test_login_without_referer_goes_to_intended_default(): void
    {
        User::factory()->create(['email' => 'user@example.com']);

        $this->post('/login', [
            'email' => 'user@example.com',
            'password' => 'password',
        ])->assertRedirect('/dashboard');
    }
}
