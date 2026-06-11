<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Material;
use App\Models\Permission;
use App\Models\Category;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

class MaterialPolicyTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::factory()->create();
    }

    #[Test]
    public function admin_can_view_any_material(): void
    {
        $admin = $this->createAdmin();
        $this->assertTrue(Gate::forUser($admin)->allows('viewAny', Material::class));
    }

    #[Test]
    public function content_manager_can_view_any_material(): void
    {
        $manager = $this->createContentManager();
        $this->assertTrue(Gate::forUser($manager)->allows('viewAny', Material::class));
    }

    #[Test]
    public function regular_user_cannot_view_any_material(): void
    {
        $user = User::factory()->create();
        $this->assertFalse(Gate::forUser($user)->allows('viewAny', Material::class));
    }

    #[Test]
    public function user_can_view_own_material(): void
    {
        $user = User::factory()->create();
        $material = $this->createMaterial($user->id);

        $this->assertTrue(Gate::forUser($user)->allows('view', $material));
    }

    #[Test]
    public function user_cannot_view_another_user_material(): void
    {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();
        $material = $this->createMaterial($anotherUser->id);

        $this->assertFalse(Gate::forUser($user)->allows('view', $material));
    }

    #[Test]
    public function content_manager_can_view_any_material_detail(): void
    {
        $manager = $this->createContentManager();
        $user = User::factory()->create();
        $material = $this->createMaterial($user->id);

        $this->assertTrue(Gate::forUser($manager)->allows('view', $material));
    }

    #[Test]
    public function admin_can_create_material(): void
    {
        $admin = $this->createAdmin();
        $this->assertTrue(Gate::forUser($admin)->allows('create', Material::class));
    }

    #[Test]
    public function content_manager_can_create_material(): void
    {
        $manager = $this->createContentManager();
        $this->assertTrue(Gate::forUser($manager)->allows('create', Material::class));
    }

    #[Test]
    public function regular_user_cannot_create_material(): void
    {
        $user = User::factory()->create();
        $this->assertFalse(Gate::forUser($user)->allows('create', Material::class));
    }

    #[Test]
    public function user_can_update_own_material(): void
    {
        $user = User::factory()->create();
        $material = $this->createMaterial($user->id);

        $this->assertTrue(Gate::forUser($user)->allows('update', $material));
    }

    #[Test]
    public function user_cannot_update_another_user_material(): void
    {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();
        $material = $this->createMaterial($anotherUser->id);

        $this->assertFalse(Gate::forUser($user)->allows('update', $material));
    }

    #[Test]
    public function content_manager_can_update_any_material(): void
    {
        $manager = $this->createContentManager();
        $user = User::factory()->create();
        $material = $this->createMaterial($user->id);

        $this->assertTrue(Gate::forUser($manager)->allows('update', $material));
    }

    #[Test]
    public function admin_can_delete_any_material(): void
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create();
        $material = $this->createMaterial($user->id);

        $this->assertTrue(Gate::forUser($admin)->allows('delete', $material));
    }

    #[Test]
    public function content_manager_can_delete_any_material(): void
    {
        $manager = $this->createContentManager();
        $user = User::factory()->create();
        $material = $this->createMaterial($user->id);

        $this->assertTrue(Gate::forUser($manager)->allows('delete', $material));
    }

    #[Test]
    public function regular_user_cannot_delete_any_material(): void
    {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();
        $material = $this->createMaterial($anotherUser->id);

        $this->assertFalse(Gate::forUser($user)->allows('delete', $material));
    }

    #[Test]
    public function admin_can_publish_material(): void
    {
        $admin = $this->createAdmin();
        $this->assertTrue(Gate::forUser($admin)->allows('publish', Material::class));
    }

    #[Test]
    public function content_manager_can_publish_material(): void
    {
        $manager = $this->createContentManager();
        $this->assertTrue(Gate::forUser($manager)->allows('publish', Material::class));
    }

    #[Test]
    public function regular_user_cannot_publish_material(): void
    {
        $user = User::factory()->create();
        $this->assertFalse(Gate::forUser($user)->allows('publish', Material::class));
    }

    #[Test]
    public function admin_can_move_to_trash(): void
    {
        $admin = $this->createAdmin();
        $this->assertTrue(Gate::forUser($admin)->allows('moveToTrash', Material::class));
    }

    #[Test]
    public function content_manager_can_move_to_trash(): void
    {
        $manager = $this->createContentManager();
        $this->assertTrue(Gate::forUser($manager)->allows('moveToTrash', Material::class));
    }

    #[Test]
    public function regular_user_cannot_move_to_trash(): void
    {
        $user = User::factory()->create();
        $this->assertFalse(Gate::forUser($user)->allows('moveToTrash', Material::class));
    }

    #[Test]
    public function admin_can_restore_material(): void
    {
        $admin = $this->createAdmin();
        $this->assertTrue(Gate::forUser($admin)->allows('restore', Material::class));
    }

    #[Test]
    public function content_manager_can_restore_material(): void
    {
        $manager = $this->createContentManager();
        $this->assertTrue(Gate::forUser($manager)->allows('restore', Material::class));
    }

    #[Test]
    public function regular_user_cannot_restore_material(): void
    {
        $user = User::factory()->create();
        $this->assertFalse(Gate::forUser($user)->allows('restore', Material::class));
    }

    #[Test]
    public function admin_can_force_delete(): void
    {
        $admin = $this->createAdmin();
        $this->assertTrue(Gate::forUser($admin)->allows('forceDelete', Material::class));
    }

    #[Test]
    public function content_manager_can_force_delete(): void
    {
        $manager = $this->createContentManager();
        $this->assertTrue(Gate::forUser($manager)->allows('forceDelete', Material::class));
    }

    #[Test]
    public function regular_user_cannot_force_delete(): void
    {
        $user = User::factory()->create();
        $this->assertFalse(Gate::forUser($user)->allows('forceDelete', Material::class));
    }

    private function createMaterial(int $userId): Material
    {
        return Material::factory()->create([
            'user_id' => $userId,
            'category_id' => $this->category->id,
            'state' => 'draft',
        ]);
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
        $materialsPermission = Permission::firstOrCreate(
            ['key' => 'materials.manage'],
            ['name' => 'Materials Manage', 'group' => 'materials']
        );

        $manager = User::factory()->create([
            'email' => 'manager_' . uniqid() . '@test.com',
        ]);

        $manager->permissions()->sync([$materialsPermission->id]);

        return $manager->fresh();
    }
}
