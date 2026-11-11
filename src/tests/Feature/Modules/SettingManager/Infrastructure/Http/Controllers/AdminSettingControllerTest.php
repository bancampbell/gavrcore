<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\SettingManager\Infrastructure\Http\Controllers;

use App\Modules\SettingManager\Application\Services\SettingService;
use App\Modules\SettingManager\Infrastructure\Models\Setting;
use App\Modules\UserManager\Infrastructure\Http\Middleware\AdminMiddleware;
use App\Modules\UserManager\Infrastructure\Models\UserModel as User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Inertia\Testing\AssertableInertia as Assert;
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

    public function test_index_exposes_only_safe_user_fields(): void
    {
        $this->actingAs($this->admin)
            ->withoutMiddleware(AdminMiddleware::class)
            ->get('/admin/settings')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('SettingManager/Settings/Index', false)
                ->where('user.id', $this->admin->id)
                ->where('user.name', $this->admin->name)
                ->where('user.email', $this->admin->email)
                ->missing('user.password')
                ->missing('user.remember_token')
            );
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

    public function test_update_save_and_close_redirects_to_dashboard_with_flash(): void
    {
        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(AdminMiddleware::class)
            ->post('/admin/settings', ['site_name' => 'Save and Close', 'close' => true]);

        $response->assertRedirect('/admin/dashboard');
        $response->assertSessionHas('success', 'Настройки сохранены');
        $this->assertDatabaseHas('settings', ['key' => 'site_name', 'value' => 'Save and Close']);
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

    // Регрессия после инцидента с капчей (2026-11-11): type='number' с нечисловым
    // значением обнулялся в getAllSettings() (тестовый ключ Turnstile '1x0000...AA' → 0).
    public function test_get_all_settings_keeps_raw_non_numeric_value_for_number_type(): void
    {
        Setting::updateOrCreate(
            ['key' => 'captcha_secret'],
            ['value' => '1x0000000000000000000000000000000AA', 'type' => 'number', 'group' => 'captcha', 'label' => 'Secret Key капчи', 'order' => 3]
        );

        $settings = app(SettingService::class)->getAllSettings();

        $this->assertSame('1x0000000000000000000000000000000AA', $settings['captcha_secret']);
    }

    // type определяется только при создании записи: save админки не должен его перезаписывать
    // (инцидент: значение '0' давало type='number'/'boolean' при is_numeric-детекте).
    public function test_update_does_not_overwrite_type_of_existing_setting(): void
    {
        Setting::updateOrCreate(
            ['key' => 'captcha_site_key'],
            ['value' => 'old-key', 'type' => 'string', 'group' => 'captcha', 'label' => 'Site Key капчи', 'order' => 2]
        );

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(AdminMiddleware::class)
            ->post('/admin/settings', ['captcha_site_key' => '0']);

        $response->assertRedirect();
        $this->assertDatabaseHas('settings', [
            'key' => 'captcha_site_key',
            'value' => '0',
            'type' => 'string',
        ]);
    }
}
