<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\AdminAudit;

use App\Modules\FormBuilder\Infrastructure\Models\FormModel;
use App\Modules\MaterialManager\Infrastructure\Models\MaterialModel;
use App\Modules\UserManager\Infrastructure\Http\Middleware\AdminMiddleware;
use App\Modules\UserManager\Infrastructure\Models\UserModel as User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\Feature\Modules\FormBuilder\Infrastructure\Models\FormModelFactory;
use Tests\Feature\Modules\MaterialManager\Infrastructure\Models\MaterialModelFactory;
use Tests\TestCase;

class AdminAuditLogTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
        Gate::before(fn (User $user) => $user->id === $this->admin->id ? true : null);
    }

    public function test_material_create_writes_admin_audit_log(): void
    {
        $this->actingAs($this->admin)
            ->withoutMiddleware(AdminMiddleware::class)
            ->postJson(route('admin.materials.store'), [
                'title' => 'Audit Material',
                'slug' => 'audit-material',
                'state' => 'draft',
                'access' => 'public',
            ])
            ->assertCreated();

        $materialId = MaterialModel::where('slug', 'audit-material')->value('id');

        $this->assertDatabaseHas('admin_audit_logs', [
            'actor' => $this->admin->id,
            'action' => 'created',
            'subject_type' => 'material',
            'subject_id' => $materialId,
        ]);
    }

    public function test_material_update_writes_admin_audit_log(): void
    {
        $material = (new MaterialModelFactory)->create([
            'user_id' => $this->admin->id,
        ]);

        $this->actingAs($this->admin)
            ->withoutMiddleware(AdminMiddleware::class)
            ->putJson(route('admin.materials.update', $material->id), [
                'title' => 'Updated Audit Material',
                'state' => 'published',
                'access' => 'public',
            ])
            ->assertOk();

        $this->assertDatabaseHas('admin_audit_logs', [
            'actor' => $this->admin->id,
            'action' => 'updated',
            'subject_type' => 'material',
            'subject_id' => $material->id,
        ]);
    }

    public function test_material_delete_writes_admin_audit_log_and_keeps_it(): void
    {
        $material = (new MaterialModelFactory)->create([
            'user_id' => $this->admin->id,
        ]);

        $this->actingAs($this->admin)
            ->withoutMiddleware(AdminMiddleware::class)
            ->deleteJson(route('admin.materials.destroy', $material->id))
            ->assertOk();

        $this->assertDatabaseHas('materials', ['id' => $material->id, 'state' => 'trash']);
        $this->assertDatabaseHas('admin_audit_logs', [
            'actor' => $this->admin->id,
            'action' => 'deleted',
            'subject_type' => 'material',
            'subject_id' => $material->id,
        ]);
    }

    public function test_user_create_writes_admin_audit_log(): void
    {
        $this->actingAs($this->admin)
            ->withoutMiddleware(AdminMiddleware::class)
            ->postJson(route('admin.users.store'), [
                'name' => 'Audit User',
                'username' => 'audit-user',
                'email' => 'audit-user@example.com',
                'password' => 'secret123',
            ])
            ->assertOk();

        $userId = User::where('email', 'audit-user@example.com')->value('id');

        $this->assertDatabaseHas('admin_audit_logs', [
            'actor' => $this->admin->id,
            'action' => 'created',
            'subject_type' => 'user',
            'subject_id' => $userId,
        ]);
    }

    public function test_user_update_writes_admin_audit_log(): void
    {
        $user = User::factory()->create(['username' => 'audit-target-update']);

        $this->actingAs($this->admin)
            ->withoutMiddleware(AdminMiddleware::class)
            ->putJson(route('admin.users.update', $user->id), [
                'name' => 'Updated Audit User',
                'username' => $user->username,
                'email' => $user->email,
            ])
            ->assertOk();

        $this->assertDatabaseHas('admin_audit_logs', [
            'actor' => $this->admin->id,
            'action' => 'updated',
            'subject_type' => 'user',
            'subject_id' => $user->id,
        ]);
    }

    public function test_user_delete_writes_admin_audit_log_and_keeps_it(): void
    {
        $user = User::factory()->create(['username' => 'audit-target-delete']);

        $this->actingAs($this->admin)
            ->withoutMiddleware(AdminMiddleware::class)
            ->deleteJson(route('admin.users.destroy', $user->id))
            ->assertOk();

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
        $this->assertDatabaseHas('admin_audit_logs', [
            'actor' => $this->admin->id,
            'action' => 'deleted',
            'subject_type' => 'user',
            'subject_id' => $user->id,
        ]);
    }

    public function test_form_create_writes_admin_audit_log(): void
    {
        $this->actingAs($this->admin)
            ->withoutMiddleware(AdminMiddleware::class)
            ->postJson('/admin/forms', ['title' => 'Audit Form'])
            ->assertOk();

        $formId = FormModel::where('title', 'Audit Form')->value('id');

        $this->assertDatabaseHas('admin_audit_logs', [
            'actor' => $this->admin->id,
            'action' => 'created',
            'subject_type' => 'form',
            'subject_id' => $formId,
        ]);
    }

    public function test_form_update_writes_admin_audit_log(): void
    {
        $form = (new FormModelFactory)->create();

        $this->actingAs($this->admin)
            ->withoutMiddleware(AdminMiddleware::class)
            ->putJson("/admin/forms/{$form->id}", ['title' => 'Updated Audit Form'])
            ->assertOk();

        $this->assertDatabaseHas('admin_audit_logs', [
            'actor' => $this->admin->id,
            'action' => 'updated',
            'subject_type' => 'form',
            'subject_id' => $form->id,
        ]);
    }

    public function test_form_status_update_writes_admin_audit_log(): void
    {
        $form = (new FormModelFactory)->create(['status' => true]);

        $this->actingAs($this->admin)
            ->withoutMiddleware(AdminMiddleware::class)
            ->putJson("/admin/forms/{$form->id}/status", ['status' => false])
            ->assertOk();

        $this->assertDatabaseHas('admin_audit_logs', [
            'actor' => $this->admin->id,
            'action' => 'updated',
            'subject_type' => 'form',
            'subject_id' => $form->id,
        ]);
    }

    public function test_form_delete_writes_admin_audit_log_and_keeps_it(): void
    {
        $form = (new FormModelFactory)->create();

        $this->actingAs($this->admin)
            ->withoutMiddleware(AdminMiddleware::class)
            ->deleteJson("/admin/forms/{$form->id}")
            ->assertOk();

        $this->assertDatabaseMissing('forms', ['id' => $form->id]);
        $this->assertDatabaseHas('admin_audit_logs', [
            'actor' => $this->admin->id,
            'action' => 'deleted',
            'subject_type' => 'form',
            'subject_id' => $form->id,
        ]);
    }
}
