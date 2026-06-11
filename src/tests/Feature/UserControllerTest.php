<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Создаём админа
        $adminPermission = Permission::firstOrCreate(
            ['key' => 'admin.access'],
            ['name' => 'Admin Access', 'group' => 'admin']
        );

        $this->admin = User::factory()->create([
            'email' => 'admin_' . uniqid() . '@test.com',
        ]);
        $this->admin->permissions()->sync([$adminPermission->id]);

        // Создаём обычного пользователя
        $this->user = User::factory()->create();
    }

    #[Test]
    public function admin_can_view_users_index(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/users');

        $response->assertStatus(200);
    }

    #[Test]
    public function regular_user_cannot_view_users_index(): void
    {
        $response = $this->actingAs($this->user)
            ->get('/admin/users');

        $response->assertStatus(403);
    }

    #[Test]
    public function guest_cannot_view_users_index(): void
    {
        $response = $this->get('/admin/users');

        $response->assertStatus(302);
    }

    #[Test]
    public function admin_can_create_user(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/users', [
                'name' => 'New User',
                'username' => 'newuser',
                'email' => 'newuser@test.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertRedirect('/admin/users');
        $this->assertDatabaseHas('users', [
            'email' => 'newuser@test.com',
        ]);
    }

    #[Test]
    public function regular_user_cannot_create_user(): void
    {
        $response = $this->actingAs($this->user)
            ->post('/admin/users', [
                'name' => 'New User',
                'username' => 'newuser',
                'email' => 'newuser@test.com',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function admin_can_update_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->put("/admin/users/{$user->id}", [
                'name' => 'Updated Name',
                'username' => $user->username,
                'email' => $user->email,
            ]);

        $response->assertRedirect('/admin/users');
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
        ]);
    }

    #[Test]
    public function user_cannot_update_another_user(): void
    {
        $anotherUser = User::factory()->create();

        $response = $this->actingAs($this->user)
            ->put("/admin/users/{$anotherUser->id}", [
                'name' => 'Hacked Name',
                'username' => $anotherUser->username,
                'email' => $anotherUser->email,
            ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function admin_can_delete_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->delete("/admin/users/{$user->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    #[Test]
    public function regular_user_cannot_delete_user(): void
    {
        $anotherUser = User::factory()->create();

        $response = $this->actingAs($this->user)
            ->delete("/admin/users/{$anotherUser->id}");

        $response->assertStatus(403);
    }

    #[Test]
    public function admin_can_bulk_block_users(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $response = $this->actingAs($this->admin)
            ->post('/admin/users/bulk-block', [
                'ids' => [$user1->id, $user2->id],
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'id' => $user1->id,
            'blocked' => true,
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $user2->id,
            'blocked' => true,
        ]);
    }

    #[Test]
    public function regular_user_cannot_bulk_block_users(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $response = $this->actingAs($this->user)
            ->post('/admin/users/bulk-block', [
                'ids' => [$user1->id, $user2->id],
            ]);

        $response->assertStatus(403);
    }

    #[Test]
    public function admin_can_bulk_unblock_users(): void
    {
        $user1 = User::factory()->create(['blocked' => true]);
        $user2 = User::factory()->create(['blocked' => true]);

        $response = $this->actingAs($this->admin)
            ->post('/admin/users/bulk-unblock', [
                'ids' => [$user1->id, $user2->id],
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'id' => $user1->id,
            'blocked' => false,
        ]);
        $this->assertDatabaseHas('users', [
            'id' => $user2->id,
            'blocked' => false,
        ]);
    }
}
