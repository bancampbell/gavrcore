<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\MaterialManager\Infrastructure\Http\Controllers;

use App\Modules\SettingManager\Infrastructure\Models\Setting;
use App\Modules\UserManager\Infrastructure\Http\Middleware\AdminMiddleware;
use App\Modules\UserManager\Infrastructure\Models\UserModel as User;
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
        return [AdminMiddleware::class];
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
            ->assertCreated()
            ->assertJsonPath('success', true);
    }

    public function test_edit_returns_inertia_page(): void
    {
        $material = (new MaterialModelFactory)->create();

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
        $material = (new MaterialModelFactory)->create([
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

    public function test_update_with_show_on_homepage_assigns_new_homepage_material(): void
    {
        $current = (new MaterialModelFactory)->published()->create([
            'user_id' => $this->admin->id,
            'show_on_homepage' => true,
        ]);
        $next = (new MaterialModelFactory)->published()->create([
            'user_id' => $this->admin->id,
        ]);

        Setting::updateOrCreate(
            ['key' => 'homepage_type'],
            ['value' => 'material', 'type' => 'string', 'group' => 'general', 'label' => 'Тип главной страницы', 'order' => 4]
        );
        Setting::updateOrCreate(
            ['key' => 'homepage_material_id'],
            ['value' => (string) $current->id, 'type' => 'number', 'group' => 'general', 'label' => 'Главный материал', 'order' => 5]
        );

        $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->put(route('admin.materials.update', $next->id), [
                'show_on_homepage' => true,
            ]);

        // Главной назначен новый материал, прежний снят
        $this->assertDatabaseHas('settings', ['key' => 'homepage_type', 'value' => 'material']);
        $this->assertDatabaseHas('settings', ['key' => 'homepage_material_id', 'value' => (string) $next->id]);
        $this->assertDatabaseHas('materials', ['id' => $next->id, 'show_on_homepage' => true]);
        $this->assertDatabaseHas('materials', ['id' => $current->id, 'show_on_homepage' => false]);
    }

    public function test_update_with_show_on_homepage_false_removes_homepage(): void
    {
        $material = (new MaterialModelFactory)->published()->create([
            'user_id' => $this->admin->id,
            'show_on_homepage' => true,
        ]);

        Setting::updateOrCreate(
            ['key' => 'homepage_type'],
            ['value' => 'material', 'type' => 'string', 'group' => 'general', 'label' => 'Тип главной страницы', 'order' => 4]
        );
        Setting::updateOrCreate(
            ['key' => 'homepage_material_id'],
            ['value' => (string) $material->id, 'type' => 'number', 'group' => 'general', 'label' => 'Главный материал', 'order' => 5]
        );

        $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->put(route('admin.materials.update', $material->id), [
                'show_on_homepage' => false,
            ]);

        // Главная снята: тип переключён на стандартную страницу, флаг сброшен
        $this->assertDatabaseHas('settings', ['key' => 'homepage_type', 'value' => 'default']);
        $this->assertDatabaseHas('materials', ['id' => $material->id, 'show_on_homepage' => false]);

        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Index', false)
                ->where('homepageMaterial', null)
            );
    }

    public function test_toggle_homepage_changes_material_shown_on_homepage(): void
    {
        $first = (new MaterialModelFactory)->published()->create(['user_id' => $this->admin->id]);
        $second = (new MaterialModelFactory)->published()->create(['user_id' => $this->admin->id]);

        Setting::updateOrCreate(
            ['key' => 'homepage_type'],
            ['value' => 'material', 'type' => 'string', 'group' => 'general', 'label' => 'Тип главной страницы', 'order' => 4]
        );
        Setting::updateOrCreate(
            ['key' => 'homepage_material_id'],
            ['value' => (string) $first->id, 'type' => 'number', 'group' => 'general', 'label' => 'Главный материал', 'order' => 5]
        );

        $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->put(route('admin.materials.update', $second->id), [
                'show_on_homepage' => true,
            ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Index', false)
                ->where('homepageMaterial.id', $second->id)
            );
    }

    public function test_edit_checkbox_reflects_homepage_setting_not_material_flag(): void
    {
        // Флаг в БД намеренно рассинхронизирован с настройками:
        // главный по настройкам — $homepage (флаг false), у $other флаг true
        $homepage = (new MaterialModelFactory)->published()->create([
            'user_id' => $this->admin->id,
            'show_on_homepage' => false,
        ]);
        $other = (new MaterialModelFactory)->published()->create([
            'user_id' => $this->admin->id,
            'show_on_homepage' => true,
        ]);

        Setting::updateOrCreate(
            ['key' => 'homepage_type'],
            ['value' => 'material', 'type' => 'string', 'group' => 'general', 'label' => 'Тип главной страницы', 'order' => 4]
        );
        Setting::updateOrCreate(
            ['key' => 'homepage_material_id'],
            ['value' => (string) $homepage->id, 'type' => 'number', 'group' => 'general', 'label' => 'Главный материал', 'order' => 5]
        );

        $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->get(route('admin.materials.edit', $homepage->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('MaterialManager/Edit', false)
                ->where('material.id', $homepage->id)
                ->where('material.show_on_homepage', true)
            );

        $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->get(route('admin.materials.edit', $other->id))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('MaterialManager/Edit', false)
                ->where('material.id', $other->id)
                ->where('material.show_on_homepage', false)
            );
    }

    public function test_bulk_trash_moves_materials_to_trash(): void
    {
        $materials = (new MaterialModelFactory)->count(3)->create();

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

    public function test_destroy_moves_material_to_trash(): void
    {
        // Материал от имени админа, чтобы Policy moveToTrash пропустила
        $material = (new MaterialModelFactory)->create([
            'state' => 'published',
            'user_id' => $this->admin->id,
        ]);

        $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->deleteJson(route('admin.materials.destroy', $material->id))
            ->assertOk()
            ->assertJsonPath('message', 'Материал перемещён в корзину');

        $this->assertDatabaseHas('materials', [
            'id' => $material->id,
            'state' => 'trash',
        ]);
    }

    public function test_destroy_returns_404_for_missing_material(): void
    {
        $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->deleteJson(route('admin.materials.destroy', 999999))
            ->assertNotFound();
    }

    public function test_restore_recoveres_materials(): void
    {
        $materials = (new MaterialModelFactory)->count(2)->create([
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
        $materials = (new MaterialModelFactory)->count(2)->create([
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
        (new MaterialModelFactory)->count(3)->create(['state' => 'trash', 'deleted_at' => now()]);
        (new MaterialModelFactory)->create(['state' => 'published']);

        $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->postJson(route('admin.materials.empty-trash'))
            ->assertOk();

        $this->assertDatabaseCount('materials', 1);
    }

    public function test_bulk_publish_publishes_materials(): void
    {
        $materials = (new MaterialModelFactory)->count(2)->create(['state' => 'draft']);

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
        $materials = (new MaterialModelFactory)->count(2)->create(['state' => 'published']);

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
