<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Material;
use App\Models\Category;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MaterialControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $manager;
    private User $user;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        // Создаём категорию для тестов
        $this->category = Category::factory()->create();

        // Создаём админа
        $adminPermission = Permission::firstOrCreate(
            ['key' => 'admin.access'],
            ['name' => 'Admin Access', 'group' => 'admin']
        );
        $this->admin = User::factory()->create(['email' => 'admin_' . uniqid() . '@test.com']);
        $this->admin->permissions()->sync([$adminPermission->id]);

        // Создаём менеджера контента
        $managerPermission = Permission::firstOrCreate(
            ['key' => 'materials.manage'],
            ['name' => 'Materials Manage', 'group' => 'materials']
        );
        $this->manager = User::factory()->create(['email' => 'manager_' . uniqid() . '@test.com']);
        $this->manager->permissions()->sync([$managerPermission->id]);

        // Создаём обычного пользователя
        $this->user = User::factory()->create();
    }

    private function createMaterial(int $userId, string $state = 'draft'): Material
    {
        return Material::factory()->create([
            'user_id' => $userId,
            'category_id' => $this->category->id,
            'state' => $state,
        ]);
    }

    #[Test]
    public function admin_can_view_materials_index(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/materials');

        $response->assertStatus(200);
    }

    #[Test]
    public function manager_can_view_materials_index(): void
    {
        $response = $this->actingAs($this->manager)
            ->get('/admin/materials');

        $response->assertStatus(200);
    }

    #[Test]
    public function regular_user_cannot_view_materials_index(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/admin/materials');

        $response->assertStatus(403);
    }

    #[Test]
    public function admin_can_create_material(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/materials', [
                'title' => 'Test Material',
                'category_id' => $this->category->id,
                'state' => 'draft',
            ]);

        $response->assertRedirect('/admin/materials');
        $this->assertDatabaseHas('materials', [
            'title' => 'Test Material',
        ]);
    }

    #[Test]
    public function manager_can_create_material(): void
    {
        $response = $this->actingAs($this->manager)
            ->post('/admin/materials', [
                'title' => 'Manager Material',
                'category_id' => $this->category->id,
                'state' => 'draft',
            ]);

        $response->assertRedirect('/admin/materials');
        $this->assertDatabaseHas('materials', [
            'title' => 'Manager Material',
        ]);
    }

    #[Test]
    public function regular_user_cannot_create_material(): void
    {
        $response = $this->actingAs($this->user)
            ->post('/admin/materials', [
                'title' => 'Hacked Material',
                'category_id' => $this->category->id,
                'state' => 'draft',
            ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function user_can_edit_own_material(): void
    {
        $material = $this->createMaterial($this->user->id);

        $response = $this->actingAs($this->user)
            ->get("/admin/materials/{$material->id}/edit");

        $response->assertStatus(200);
    }

    #[Test]
    public function user_cannot_edit_another_user_material(): void
    {
        $material = $this->createMaterial($this->admin->id);

        $response = $this->actingAs($this->user)
            ->get("/admin/materials/{$material->id}/edit");

        $response->assertStatus(403);
    }

    #[Test]
    public function manager_can_edit_any_material(): void
    {
        $material = $this->createMaterial($this->user->id);

        $response = $this->actingAs($this->manager)
            ->get("/admin/materials/{$material->id}/edit");

        $response->assertStatus(200);
    }

    #[Test]
    public function user_can_update_own_material(): void
    {
        $material = $this->createMaterial($this->user->id);

        $response = $this->actingAs($this->user)
            ->put("/admin/materials/{$material->id}", [
                'title' => 'Updated Title',
                'category_id' => $this->category->id,
                'state' => 'draft',
            ]);

        $response->assertRedirect('/admin/materials');
        $this->assertDatabaseHas('materials', [
            'id' => $material->id,
            'title' => 'Updated Title',
        ]);
    }

    #[Test]
    public function user_cannot_update_another_user_material(): void
    {
        $material = $this->createMaterial($this->admin->id);

        $response = $this->actingAs($this->user)
            ->put("/admin/materials/{$material->id}", [
                'title' => 'Hacked Title',
                'category_id' => $this->category->id,
                'state' => 'draft',
            ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function admin_can_move_material_to_trash(): void
    {
        $material = $this->createMaterial($this->user->id);

        $response = $this->actingAs($this->admin)
            ->post('/admin/materials/bulk-trash', [
                'ids' => [$material->id],
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('materials', [
            'id' => $material->id,
            'state' => 'trash',
        ]);
    }

    #[Test]
    public function manager_can_move_material_to_trash(): void
    {
        $material = $this->createMaterial($this->user->id);

        $response = $this->actingAs($this->manager)
            ->post('/admin/materials/bulk-trash', [
                'ids' => [$material->id],
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('materials', [
            'id' => $material->id,
            'state' => 'trash',
        ]);
    }

    #[Test]
    public function user_cannot_move_material_to_trash(): void
    {
        $material = $this->createMaterial($this->user->id);

        $response = $this->actingAs($this->user)
            ->post('/admin/materials/bulk-trash', [
                'ids' => [$material->id],
            ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function admin_can_bulk_publish_materials(): void
    {
        $material1 = $this->createMaterial($this->user->id, 'draft');
        $material2 = $this->createMaterial($this->user->id, 'draft');

        $response = $this->actingAs($this->admin)
            ->post('/admin/materials/bulk-publish', [
                'ids' => [$material1->id, $material2->id],
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('materials', [
            'id' => $material1->id,
            'state' => 'published',
        ]);
        $this->assertDatabaseHas('materials', [
            'id' => $material2->id,
            'state' => 'published',
        ]);
    }

    #[Test]
    public function manager_can_bulk_publish_materials(): void
    {
        $material1 = $this->createMaterial($this->user->id, 'draft');
        $material2 = $this->createMaterial($this->user->id, 'draft');

        $response = $this->actingAs($this->manager)
            ->post('/admin/materials/bulk-publish', [
                'ids' => [$material1->id, $material2->id],
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('materials', [
            'id' => $material1->id,
            'state' => 'published',
        ]);
    }

    #[Test]
    public function regular_user_cannot_bulk_publish_materials(): void
    {
        $material1 = $this->createMaterial($this->user->id, 'draft');

        $response = $this->actingAs($this->user)
            ->post('/admin/materials/bulk-publish', [
                'ids' => [$material1->id],
            ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function admin_can_bulk_unpublish_materials(): void
    {
        $material1 = $this->createMaterial($this->user->id, 'published');
        $material2 = $this->createMaterial($this->user->id, 'published');

        $response = $this->actingAs($this->admin)
            ->post('/admin/materials/bulk-unpublish', [
                'ids' => [$material1->id, $material2->id],
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('materials', [
            'id' => $material1->id,
            'state' => 'draft',
        ]);
    }

    #[Test]
    public function admin_can_view_trash_page(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/materials/trash');

        $response->assertStatus(200);
    }

    #[Test]
    public function manager_can_view_trash_page(): void
    {
        $response = $this->actingAs($this->manager)
            ->get('/admin/materials/trash');

        $response->assertStatus(200);
    }

    #[Test]
    public function regular_user_cannot_view_trash_page(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/admin/materials/trash');

        $response->assertStatus(403);
    }

    #[Test]
    public function admin_can_restore_material_from_trash(): void
    {
        $material = $this->createMaterial($this->user->id, 'trash');

        $response = $this->actingAs($this->admin)
            ->post('/admin/materials/restore', [
                'ids' => [$material->id],
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('materials', [
            'id' => $material->id,
            'state' => 'draft',
        ]);
    }

    #[Test]
    public function admin_can_force_delete_material(): void
    {
        $material = $this->createMaterial($this->user->id, 'trash');

        $response = $this->actingAs($this->admin)
            ->post('/admin/materials/force-delete', [
                'ids' => [$material->id],
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseMissing('materials', [
            'id' => $material->id,
        ]);
    }

    #[Test]
    public function admin_can_empty_trash(): void
    {
        $material1 = $this->createMaterial($this->user->id, 'trash');
        $material2 = $this->createMaterial($this->user->id, 'trash');

        $response = $this->actingAs($this->admin)
            ->post('/admin/materials/empty-trash');

        $response->assertStatus(200);
        $this->assertDatabaseMissing('materials', [
            'id' => $material1->id,
        ]);
        $this->assertDatabaseMissing('materials', [
            'id' => $material2->id,
        ]);
    }
}
