<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Group;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GroupControllerTest extends TestCase
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

        // Создаём менеджера контента (не имеет прав на группы)
        $managerPermission = Permission::firstOrCreate(
            ['key' => 'materials.manage'],
            ['name' => 'Materials Manage', 'group' => 'materials']
        );
        $this->manager = User::factory()->create(['email' => 'manager_' . uniqid() . '@test.com']);
        $this->manager->permissions()->sync([$managerPermission->id]);

        // Создаём обычного пользователя
        $this->user = User::factory()->create();
    }

    #[Test]
    public function admin_can_view_groups_index(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/groups');

        $response->assertStatus(200);
    }

    #[Test]
    public function manager_cannot_view_groups_index(): void
    {
        $response = $this->actingAs($this->manager)
            ->get('/admin/groups');

        $response->assertStatus(403);
    }

    #[Test]
    public function regular_user_cannot_view_groups_index(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/admin/groups');

        $response->assertStatus(403);
    }

    #[Test]
    public function admin_can_create_group(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/groups', [
                'name' => 'Test Group',
                'status' => true,
            ]);

        $response->assertRedirect('/admin/groups');
        $this->assertDatabaseHas('groups', [
            'name' => 'Test Group',
        ]);
    }

    #[Test]
    public function manager_cannot_create_group(): void
    {
        $response = $this->actingAs($this->manager)
            ->post('/admin/groups', [
                'name' => 'Manager Group',
                'status' => true,
            ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function admin_can_update_group(): void
    {
        $group = Group::factory()->create();

        $response = $this->actingAs($this->admin)
            ->put("/admin/groups/{$group->id}", [
                'name' => 'Updated Group',
                'alias' => $group->alias,
                'status' => true,
            ]);

        $response->assertRedirect('/admin/groups');
        $this->assertDatabaseHas('groups', [
            'id' => $group->id,
            'name' => 'Updated Group',
        ]);
    }

    #[Test]
    public function manager_cannot_update_group(): void
    {
        $group = Group::factory()->create();

        $response = $this->actingAs($this->manager)
            ->put("/admin/groups/{$group->id}", [
                'name' => 'Hacked',
                'alias' => $group->alias,
                'status' => true,
            ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function admin_can_delete_group(): void
    {
        $group = Group::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete("/admin/groups/{$group->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('groups', [
            'id' => $group->id,
        ]);
    }

    #[Test]
    public function manager_cannot_delete_group(): void
    {
        $group = Group::factory()->create();

        $response = $this->actingAs($this->manager)
            ->delete("/admin/groups/{$group->id}");

        $response->assertStatus(403);
    }

    #[Test]
    public function admin_can_update_group_status(): void
    {
        $group = Group::factory()->create(['status' => false]);

        $response = $this->actingAs($this->admin)
            ->post("/admin/groups/{$group->id}/status");

        $response->assertStatus(200);
        $this->assertDatabaseHas('groups', [
            'id' => $group->id,
            'status' => true,
        ]);
    }
}
