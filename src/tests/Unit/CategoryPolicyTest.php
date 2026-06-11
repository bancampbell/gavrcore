<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Category;
use App\Models\Permission;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

class CategoryPolicyTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function admin_can_view_any_category(): void
    {
        $admin = $this->createAdmin();
        $this->assertTrue(Gate::forUser($admin)->allows('viewAny', Category::class));
    }

    #[Test]
    public function content_manager_can_view_any_category(): void
    {
        $manager = $this->createContentManager();
        $this->assertTrue(Gate::forUser($manager)->allows('viewAny', Category::class));
    }

    #[Test]
    public function regular_user_cannot_view_any_category(): void
    {
        $user = User::factory()->create();
        $this->assertFalse(Gate::forUser($user)->allows('viewAny', Category::class));
    }

    #[Test]
    public function admin_can_view_category_detail(): void
    {
        $admin = $this->createAdmin();
        $category = Category::factory()->create();
        $this->assertTrue(Gate::forUser($admin)->allows('view', $category));
    }

    #[Test]
    public function content_manager_can_view_category_detail(): void
    {
        $manager = $this->createContentManager();
        $category = Category::factory()->create();
        $this->assertTrue(Gate::forUser($manager)->allows('view', $category));
    }

    #[Test]
    public function regular_user_cannot_view_category_detail(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $this->assertFalse(Gate::forUser($user)->allows('view', $category));
    }

    #[Test]
    public function admin_can_create_category(): void
    {
        $admin = $this->createAdmin();
        $this->assertTrue(Gate::forUser($admin)->allows('create', Category::class));
    }

    #[Test]
    public function content_manager_can_create_category(): void
    {
        $manager = $this->createContentManager();
        $this->assertTrue(Gate::forUser($manager)->allows('create', Category::class));
    }

    #[Test]
    public function regular_user_cannot_create_category(): void
    {
        $user = User::factory()->create();
        $this->assertFalse(Gate::forUser($user)->allows('create', Category::class));
    }

    #[Test]
    public function admin_can_update_category(): void
    {
        $admin = $this->createAdmin();
        $category = Category::factory()->create();
        $this->assertTrue(Gate::forUser($admin)->allows('update', $category));
    }

    #[Test]
    public function content_manager_can_update_category(): void
    {
        $manager = $this->createContentManager();
        $category = Category::factory()->create();
        $this->assertTrue(Gate::forUser($manager)->allows('update', $category));
    }

    #[Test]
    public function regular_user_cannot_update_category(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $this->assertFalse(Gate::forUser($user)->allows('update', $category));
    }

    #[Test]
    public function admin_can_delete_category(): void
    {
        $admin = $this->createAdmin();
        $category = Category::factory()->create();
        $this->assertTrue(Gate::forUser($admin)->allows('delete', $category));
    }

    #[Test]
    public function content_manager_can_delete_category(): void
    {
        $manager = $this->createContentManager();
        $category = Category::factory()->create();
        $this->assertTrue(Gate::forUser($manager)->allows('delete', $category));
    }

    #[Test]
    public function regular_user_cannot_delete_category(): void
    {
        $user = User::factory()->create();
        $category = Category::factory()->create();
        $this->assertFalse(Gate::forUser($user)->allows('delete', $category));
    }

    private function createAdmin(): User
    {
        $adminPermission = Permission::firstOrCreate(
            ['key' => 'admin.access'],
            ['name' => 'Admin Access', 'group' => 'admin']
        );

        $admin = User::factory()->create([
            'email' => 'admin_' . uniqid() . '@test.com',
        ]);

        $admin->permissions()->sync([$adminPermission->id]);

        return $admin->fresh();
    }

    private function createContentManager(): User
    {
        $categoriesPermission = Permission::firstOrCreate(
            ['key' => 'categories.manage'],
            ['name' => 'Categories Manage', 'group' => 'categories']
        );

        $manager = User::factory()->create([
            'email' => 'manager_' . uniqid() . '@test.com',
        ]);

        $manager->permissions()->sync([$categoriesPermission->id]);

        return $manager->fresh();
    }
}
