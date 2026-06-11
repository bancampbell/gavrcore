<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Group;
use App\Models\Permission;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

class GroupPolicyTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function admin_can_view_any_group(): void
    {
        $admin = $this->createAdmin();
        $this->assertTrue(Gate::forUser($admin)->allows('viewAny', Group::class));
    }

    #[Test]
    public function content_manager_cannot_view_any_group(): void
    {
        $manager = $this->createContentManager();
        $this->assertFalse(Gate::forUser($manager)->allows('viewAny', Group::class));
    }

    #[Test]
    public function regular_user_cannot_view_any_group(): void
    {
        $user = User::factory()->create();
        $this->assertFalse(Gate::forUser($user)->allows('viewAny', Group::class));
    }

    #[Test]
    public function admin_can_view_group_detail(): void
    {
        $admin = $this->createAdmin();
        $group = Group::factory()->create();
        $this->assertTrue(Gate::forUser($admin)->allows('view', $group));
    }

    #[Test]
    public function content_manager_cannot_view_group_detail(): void
    {
        $manager = $this->createContentManager();
        $group = Group::factory()->create();
        $this->assertFalse(Gate::forUser($manager)->allows('view', $group));
    }

    #[Test]
    public function regular_user_cannot_view_group_detail(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        $this->assertFalse(Gate::forUser($user)->allows('view', $group));
    }

    #[Test]
    public function admin_can_create_group(): void
    {
        $admin = $this->createAdmin();
        $this->assertTrue(Gate::forUser($admin)->allows('create', Group::class));
    }

    #[Test]
    public function content_manager_cannot_create_group(): void
    {
        $manager = $this->createContentManager();
        $this->assertFalse(Gate::forUser($manager)->allows('create', Group::class));
    }

    #[Test]
    public function regular_user_cannot_create_group(): void
    {
        $user = User::factory()->create();
        $this->assertFalse(Gate::forUser($user)->allows('create', Group::class));
    }

    #[Test]
    public function admin_can_update_group(): void
    {
        $admin = $this->createAdmin();
        $group = Group::factory()->create();
        $this->assertTrue(Gate::forUser($admin)->allows('update', $group));
    }

    #[Test]
    public function content_manager_cannot_update_group(): void
    {
        $manager = $this->createContentManager();
        $group = Group::factory()->create();
        $this->assertFalse(Gate::forUser($manager)->allows('update', $group));
    }

    #[Test]
    public function regular_user_cannot_update_group(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        $this->assertFalse(Gate::forUser($user)->allows('update', $group));
    }

    #[Test]
    public function admin_can_delete_group(): void
    {
        $admin = $this->createAdmin();
        $group = Group::factory()->create();
        $this->assertTrue(Gate::forUser($admin)->allows('delete', $group));
    }

    #[Test]
    public function content_manager_cannot_delete_group(): void
    {
        $manager = $this->createContentManager();
        $group = Group::factory()->create();
        $this->assertFalse(Gate::forUser($manager)->allows('delete', $group));
    }

    #[Test]
    public function regular_user_cannot_delete_group(): void
    {
        $user = User::factory()->create();
        $group = Group::factory()->create();
        $this->assertFalse(Gate::forUser($user)->allows('delete', $group));
    }

    #[Test]
    public function admin_can_publish_group(): void
    {
        $admin = $this->createAdmin();
        $this->assertTrue(Gate::forUser($admin)->allows('publish', Group::class));
    }

    #[Test]
    public function content_manager_cannot_publish_group(): void
    {
        $manager = $this->createContentManager();
        $this->assertFalse(Gate::forUser($manager)->allows('publish', Group::class));
    }

    #[Test]
    public function admin_can_unpublish_group(): void
    {
        $admin = $this->createAdmin();
        $this->assertTrue(Gate::forUser($admin)->allows('unpublish', Group::class));
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
