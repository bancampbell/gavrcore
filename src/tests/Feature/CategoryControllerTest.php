<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Category;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $manager;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Создаём админа
        $adminPermission = Permission::firstOrCreate(
            ['key' => 'admin.access'],
            ['name' => 'Admin Access', 'group' => 'admin']
        );
        $this->admin = User::factory()->create(['email' => 'admin_' . uniqid() . '@test.com']);
        $this->admin->permissions()->sync([$adminPermission->id]);

        // Создаём менеджера контента
        $managerPermission = Permission::firstOrCreate(
            ['key' => 'categories.manage'],
            ['name' => 'Categories Manage', 'group' => 'categories']
        );
        $this->manager = User::factory()->create(['email' => 'manager_' . uniqid() . '@test.com']);
        $this->manager->permissions()->sync([$managerPermission->id]);

        // Создаём обычного пользователя
        $this->user = User::factory()->create();
    }

    #[Test]
    public function admin_can_view_categories_index(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/categories');

        $response->assertStatus(200);
    }

    #[Test]
    public function manager_can_view_categories_index(): void
    {
        $response = $this->actingAs($this->manager)
            ->get('/admin/categories');

        $response->assertStatus(200);
    }

    #[Test]
    public function regular_user_cannot_view_categories_index(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/admin/categories');

        $response->assertStatus(403);
    }

    #[Test]
    public function admin_can_create_category(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/categories', [
                'name' => 'Test Category',
                'is_active' => true,
            ]);

        $response->assertRedirect('/admin/categories');
        $this->assertDatabaseHas('categories', [
            'name' => 'Test Category',
        ]);
    }

    #[Test]
    public function manager_can_create_category(): void
    {
        $response = $this->actingAs($this->manager)
            ->post('/admin/categories', [
                'name' => 'Manager Category',
                'is_active' => true,
            ]);

        $response->assertRedirect('/admin/categories');
        $this->assertDatabaseHas('categories', [
            'name' => 'Manager Category',
        ]);
    }

    #[Test]
    public function regular_user_cannot_create_category(): void
    {
        $response = $this->actingAs($this->user)
            ->post('/admin/categories', [
                'name' => 'Hacked Category',
                'is_active' => true,
            ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function admin_can_update_category(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->admin)
            ->put("/admin/categories/{$category->id}", [
                'name' => 'Updated Category',
                'alias' => $category->alias,
                'is_active' => true,
            ]);

        $response->assertRedirect('/admin/categories');
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Category',
        ]);
    }

    #[Test]
    public function manager_can_update_category(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->manager)
            ->put("/admin/categories/{$category->id}", [
                'name' => 'Manager Updated',
                'alias' => $category->alias,
                'is_active' => true,
            ]);

        $response->assertRedirect('/admin/categories');
        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Manager Updated',
        ]);
    }

    #[Test]
    public function regular_user_cannot_update_category(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->user)
            ->put("/admin/categories/{$category->id}", [
                'name' => 'Hacked',
                'alias' => $category->alias,
                'is_active' => true,
            ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function admin_can_delete_category(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete("/admin/categories/{$category->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }

    #[Test]
    public function manager_can_delete_category(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->manager)
            ->delete("/admin/categories/{$category->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('categories', [
            'id' => $category->id,
        ]);
    }

    #[Test]
    public function regular_user_cannot_delete_category(): void
    {
        $category = Category::factory()->create();

        $response = $this->actingAs($this->user)
            ->delete("/admin/categories/{$category->id}");

        $response->assertStatus(403);
    }
}
