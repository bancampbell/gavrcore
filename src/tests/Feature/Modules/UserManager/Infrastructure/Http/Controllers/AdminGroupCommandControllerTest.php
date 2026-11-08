<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\UserManager\Infrastructure\Http\Controllers;

use App\Modules\UserManager\Infrastructure\Http\Middleware\AdminMiddleware;
use App\Modules\UserManager\Infrastructure\Models\UserModel as User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class AdminGroupCommandControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
        Gate::before(fn (User $user) => $user->id === $this->admin->id ? true : null);
    }

    public function test_store_returns_json_for_xhr_request(): void
    {
        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(AdminMiddleware::class)
            ->postJson(route('admin.groups.store'), [
                'name' => 'Новая группа',
            ]);

        $response->assertOk()
            ->assertJson(['success' => true, 'message' => 'Группа создана']);

        $this->assertDatabaseHas('groups', ['name' => 'Новая группа']);
    }

    public function test_store_redirects_for_non_json_request(): void
    {
        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(AdminMiddleware::class)
            ->post(route('admin.groups.store'), [
                'name' => 'Новая группа',
            ]);

        $response->assertRedirect(route('admin.groups.index'))
            ->assertSessionHas('success', 'Группа создана');

        $this->assertDatabaseHas('groups', ['name' => 'Новая группа']);
    }
}
