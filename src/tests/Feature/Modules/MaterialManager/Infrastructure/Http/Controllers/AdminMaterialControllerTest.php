<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\MaterialManager\Infrastructure\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Feature\Modules\MaterialManager\Infrastructure\Models\MaterialModelFactory;
use Tests\TestCase;

class AdminMaterialControllerTest extends TestCase
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

    public function test_index_requires_authentication(): void
    {
        $this->get(route('admin.materials.index'))->assertRedirect('/login');
    }

    public function test_index_returns_inertia_page(): void
    {
        $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->get(route('admin.materials.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('MaterialManager/Index', false)
                ->has('materials')
                ->has('categories')
                ->has('authors')
            );
    }

    public function test_create_returns_inertia_page(): void
    {
        $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->get(route('admin.materials.create'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('MaterialManager/Create', false)
                ->has('categories')
            );
    }

    public function test_store_creates_material(): void
    {
        $response = $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->post(route('admin.materials.store'), [
                'title' => 'Test Material',
                'slug' => 'test-material',
                'state' => 'draft',
                'access' => 'public',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('materials', ['slug' => 'test-material']);
    }

    public function test_store_returns_json_for_ajax(): void
    {
        $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->withHeader('Accept', 'application/json')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('admin.materials.store'), [
                'title' => 'Ajax Material',
                'state' => 'draft',
                'access' => 'public',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_edit_returns_inertia_page(): void
    {
        $material = (new MaterialModelFactory())->create();

        $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->get(route('admin.materials.edit', $material->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('MaterialManager/Edit', false)
                ->has('material')
                ->where('material.id', $material->id)
            );
    }

    public function test_update_modifies_material(): void
    {
        // Создаём материал от имени админа, чтобы Policy update пропустила
        $material = (new MaterialModelFactory())->create([
            'title' => 'Old',
            'user_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->put(route('admin.materials.update', $material->id), [
                'title' => 'Updated',
                'state' => 'published',
                'access' => 'public',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('materials', ['id' => $material->id, 'title' => 'Updated']);
    }

    public function test_bulk_trash_moves_materials_to_trash(): void
    {
        $materials = (new MaterialModelFactory())->count(3)->create();

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->postJson(route('admin.materials.bulk-trash'), [
                'ids' => $materials->pluck('id')->toArray(),
            ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Материалы перемещены в корзину');

        foreach ($materials as $material) {
            $this->assertDatabaseHas('materials', [
                'id' => $material->id,
                'state' => 'trash',
            ]);
        }
    }

    public function test_restore_recoveres_materials(): void
    {
        $materials = (new MaterialModelFactory())->count(2)->create([
            'state' => 'trash',
            'deleted_at' => now(),
        ]);

        $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->postJson(route('admin.materials.restore'), [
                'ids' => $materials->pluck('id')->toArray(),
            ])
            ->assertOk();

        foreach ($materials as $material) {
            $this->assertDatabaseHas('materials', [
                'id' => $material->id,
                'state' => 'draft',
            ]);
        }
    }

    public function test_force_delete_permanently_removes_materials(): void
    {
        $materials = (new MaterialModelFactory())->count(2)->create([
            'state' => 'trash',
            'deleted_at' => now(),
        ]);

        $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->postJson(route('admin.materials.force-delete'), [
                'ids' => $materials->pluck('id')->toArray(),
            ])
            ->assertOk();

        foreach ($materials as $material) {
            $this->assertDatabaseMissing('materials', ['id' => $material->id]);
        }
    }

    public function test_empty_trash_removes_all_trashed(): void
    {
        (new MaterialModelFactory())->count(3)->create(['state' => 'trash', 'deleted_at' => now()]);
        (new MaterialModelFactory())->create(['state' => 'published']);

        $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->postJson(route('admin.materials.empty-trash'))
            ->assertOk();

        $this->assertDatabaseCount('materials', 1);
    }

    public function test_bulk_publish_publishes_materials(): void
    {
        $materials = (new MaterialModelFactory())->count(2)->create(['state' => 'draft']);

        $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->postJson(route('admin.materials.bulk-publish'), [
                'ids' => $materials->pluck('id')->toArray(),
            ])
            ->assertOk();

        foreach ($materials as $material) {
            $this->assertDatabaseHas('materials', ['id' => $material->id, 'state' => 'published']);
        }
    }

    public function test_bulk_unpublish_drafts_materials(): void
    {
        $materials = (new MaterialModelFactory())->count(2)->create(['state' => 'published']);

        $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->postJson(route('admin.materials.bulk-unpublish'), [
                'ids' => $materials->pluck('id')->toArray(),
            ])
            ->assertOk();

        foreach ($materials as $material) {
            $this->assertDatabaseHas('materials', ['id' => $material->id, 'state' => 'draft']);
        }
    }
}
