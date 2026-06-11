<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Permission;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;

class UserPolicyTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function admin_can_view_any_user(): void
    {
        $admin = $this->createAdmin();
        $this->assertTrue(Gate::forUser($admin)->allows('viewAny', User::class));
    }

    #[Test]
    public function regular_user_cannot_view_any_user(): void
    {
        $user = User::factory()->create();
        $this->assertFalse(Gate::forUser($user)->allows('viewAny', User::class));
    }

    #[Test]
    public function user_can_view_own_profile(): void
    {
        $user = User::factory()->create();
        $this->assertTrue(Gate::forUser($user)->allows('view', $user));
    }

    #[Test]
    public function user_cannot_view_another_user_profile(): void
    {
        $user = User::factory()->create();
        $admin = $this->createAdmin();
        $this->assertFalse(Gate::forUser($user)->allows('view', $admin));
    }

    #[Test]
    public function admin_can_delete_any_user(): void
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create();
        $this->assertTrue(Gate::forUser($admin)->allows('delete', $user));
    }

    #[Test]
    public function user_cannot_delete_another_user(): void
    {
        $user = User::factory()->create();
        $admin = $this->createAdmin();
        $this->assertFalse(Gate::forUser($user)->allows('delete', $admin));
    }

    #[Test]
    public function admin_can_delete_themselves(): void
    {
        $admin = $this->createAdmin();
        $this->assertTrue(Gate::forUser($admin)->allows('delete', $admin));
    }

    #[Test]
    public function admin_can_block_any_user(): void
    {
        $admin = $this->createAdmin();
        $user = User::factory()->create();
        $this->assertTrue(Gate::forUser($admin)->allows('block', $user));
    }

    #[Test]
    public function manager_cannot_block_user(): void
    {
        $manager = $this->createManager();
        $user = User::factory()->create();
        $this->assertFalse(Gate::forUser($manager)->allows('block', $user));
    }

    #[Test]
    public function admin_can_bulk_block_users(): void
    {
        $admin = $this->createAdmin();
        $this->assertTrue(Gate::forUser($admin)->allows('bulkBlock', User::class));
    }

    #[Test]
    public function regular_user_cannot_bulk_block_users(): void
    {
        $user = User::factory()->create();
        $this->assertFalse(Gate::forUser($user)->allows('bulkBlock', User::class));
    }

    #[Test]
    public function admin_can_bulk_unblock_users(): void
    {
        $admin = $this->createAdmin();
        $this->assertTrue(Gate::forUser($admin)->allows('bulkUnblock', User::class));
    }

    #[Test]
    public function regular_user_cannot_bulk_unblock_users(): void
    {
        $user = User::factory()->create();
        $this->assertFalse(Gate::forUser($user)->allows('bulkUnblock', User::class));
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

    private function createManager(): User
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
