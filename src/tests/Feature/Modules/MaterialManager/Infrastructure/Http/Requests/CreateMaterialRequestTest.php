<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\MaterialManager\Infrastructure\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\Feature\Modules\MaterialManager\Infrastructure\Models\MaterialModelFactory;
use Tests\TestCase;

class CreateMaterialRequestTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
        Gate::before(fn (User $user) => $user->id === $this->admin->id ? true : null);
    }

    /** @return array<class-string> */
    private function adminMiddleware(): array
    {
        return [\App\Http\Middleware\AdminMiddleware::class];
    }

    public function test_title_is_required(): void
    {
        $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->postJson(route('admin.materials.store'), ['title' => ''])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title']);
    }

    public function test_slug_must_be_unique(): void
    {
        (new MaterialModelFactory())->create(['slug' => 'existing']);

        $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->postJson(route('admin.materials.store'), [
                'title' => 'Test',
                'slug' => 'existing',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['slug']);
    }

    public function test_state_must_be_valid_enum(): void
    {
        $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->postJson(route('admin.materials.store'), [
                'title' => 'Test',
                'state' => 'invalid',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['state']);
    }

    public function test_access_must_be_valid_enum(): void
    {
        $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->postJson(route('admin.materials.store'), [
                'title' => 'Test',
                'access' => 'hacker',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['access']);
    }

    public function test_valid_data_passes(): void
    {
        $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->withHeader('Accept', 'application/json')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('admin.materials.store'), [
                'title' => 'Valid Title',
                'slug' => 'valid-slug',
                'state' => 'draft',
                'access' => 'public',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);
    }
}
