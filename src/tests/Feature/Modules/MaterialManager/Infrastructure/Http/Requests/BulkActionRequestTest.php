<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\MaterialManager\Infrastructure\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\Feature\Modules\MaterialManager\Infrastructure\Models\MaterialModelFactory;
use Tests\TestCase;

class BulkActionRequestTest extends TestCase
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

    public function test_ids_are_required(): void
    {
        $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->postJson(route('admin.materials.bulk-trash'), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['ids']);
    }

    public function test_ids_must_exist_in_database(): void
    {
        $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->postJson(route('admin.materials.bulk-trash'), [
                'ids' => [99999],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['ids.0']);
    }

    public function test_valid_ids_pass(): void
    {
        $material = (new MaterialModelFactory())->create();

        $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->postJson(route('admin.materials.bulk-trash'), [
                'ids' => [$material->id],
            ])
            ->assertOk();
    }
}
