<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\SettingManager\Infrastructure\Http\Controllers;

use App\Modules\SettingManager\Infrastructure\Models\Setting;
use App\Modules\UserManager\Infrastructure\Http\Middleware\AdminMiddleware;
use App\Modules\UserManager\Infrastructure\Models\UserModel as User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\Feature\Modules\MaterialManager\Infrastructure\Models\MaterialModelFactory;
use Tests\TestCase;

/**
 * AUDIT-16: homepage_type должен обновляться через POST /admin/settings
 * (ключ был вне белого списка валидации контроллера).
 */
class AdminSettingControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
        Gate::before(fn (User $user) => $user->id === $this->admin->id ? true : null);
    }

    public function test_update_accepts_homepage_type(): void
    {
        // Ключ уже создан миграцией — готовим запись без нарушения уникальности
        Setting::updateOrCreate(
            ['key' => 'homepage_type'],
            ['value' => 'material', 'type' => 'string', 'group' => 'general', 'label' => 'Тип главной страницы', 'order' => 4]
        );

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(AdminMiddleware::class)
            ->post('/admin/settings', ['homepage_type' => 'landing']);

        $response->assertRedirect();
        $this->assertDatabaseHas('settings', ['key' => 'homepage_type', 'value' => 'landing']);
    }

    public function test_update_keeps_accepting_general_settings(): void
    {
        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(AdminMiddleware::class)
            ->post('/admin/settings', ['site_name' => 'Новое название']);

        $response->assertRedirect();
        $this->assertDatabaseHas('settings', ['key' => 'site_name', 'value' => 'Новое название']);
    }

    public function test_update_accepts_homepage_material_id(): void
    {
        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(AdminMiddleware::class)
            ->post('/admin/settings', ['homepage_material_id' => 42]);

        $response->assertRedirect();
        $this->assertDatabaseHas('settings', ['key' => 'homepage_material_id', 'value' => '42']);
    }

    public function test_update_syncs_material_flags_with_homepage_assignment(): void
    {
        $first = (new MaterialModelFactory)->create(['show_on_homepage' => true]);
        $second = (new MaterialModelFactory)->create();

        // Назначаем главный материал через страницу настроек
        $this->actingAs($this->admin)
            ->withoutMiddleware(AdminMiddleware::class)
            ->post('/admin/settings', [
                'homepage_type' => 'material',
                'homepage_material_id' => $second->id,
            ]);

        $this->assertDatabaseHas('settings', ['key' => 'homepage_material_id', 'value' => (string) $second->id]);
        $this->assertDatabaseHas('materials', ['id' => $second->id, 'show_on_homepage' => true]);
        $this->assertDatabaseHas('materials', ['id' => $first->id, 'show_on_homepage' => false]);

        // Переключаем главную на стандартную страницу — флаги сброшены
        $this->actingAs($this->admin)
            ->withoutMiddleware(AdminMiddleware::class)
            ->post('/admin/settings', ['homepage_type' => 'default']);

        $this->assertDatabaseHas('settings', ['key' => 'homepage_type', 'value' => 'default']);
        $this->assertDatabaseHas('materials', ['id' => $second->id, 'show_on_homepage' => false]);
        $this->assertDatabaseHas('materials', ['id' => $first->id, 'show_on_homepage' => false]);
    }
}
