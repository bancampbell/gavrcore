<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\FormBuilder\Infrastructure\Http\Controllers;

use App\Modules\UserManager\Infrastructure\Http\Middleware\AdminMiddleware;
use App\Modules\UserManager\Infrastructure\Models\UserModel as User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Feature\Modules\FormBuilder\Infrastructure\Models\FormModelFactory;
use Tests\TestCase;

/**
 * Тумблер captcha_enabled формы: цепочка admin store/update/edit.
 */
class AdminFormCaptchaToggleTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
        Gate::before(fn (User $user) => $user->id === $this->admin->id ? true : null);
    }

    public function test_store_persists_captcha_enabled(): void
    {
        $this->actingAs($this->admin)
            ->withoutMiddleware(AdminMiddleware::class)
            ->postJson('/admin/forms', [
                'title' => 'Форма с капчей',
                'captcha_enabled' => true,
            ])
            ->assertOk();

        $this->assertDatabaseHas('forms', [
            'title' => 'Форма с капчей',
            'captcha_enabled' => true,
        ]);
    }

    public function test_store_defaults_captcha_enabled_to_false(): void
    {
        $this->actingAs($this->admin)
            ->withoutMiddleware(AdminMiddleware::class)
            ->postJson('/admin/forms', ['title' => 'Обычная форма'])
            ->assertOk();

        $this->assertDatabaseHas('forms', [
            'title' => 'Обычная форма',
            'captcha_enabled' => false,
        ]);
    }

    public function test_store_rejects_non_boolean_captcha_enabled(): void
    {
        $this->actingAs($this->admin)
            ->withoutMiddleware(AdminMiddleware::class)
            ->postJson('/admin/forms', [
                'title' => 'Форма',
                'captcha_enabled' => 'yes',
            ])
            ->assertStatus(422);
    }

    public function test_update_toggles_captcha_enabled(): void
    {
        $form = (new FormModelFactory)->create(['captcha_enabled' => false]);

        $this->actingAs($this->admin)
            ->withoutMiddleware(AdminMiddleware::class)
            ->putJson("/admin/forms/{$form->id}", [
                'title' => $form->title,
                'captcha_enabled' => true,
            ])
            ->assertOk()
            ->assertJsonPath('form.captchaEnabled', true);

        $this->assertDatabaseHas('forms', ['id' => $form->id, 'captcha_enabled' => true]);

        // Обратно — тумблер не залипает.
        $this->actingAs($this->admin)
            ->withoutMiddleware(AdminMiddleware::class)
            ->putJson("/admin/forms/{$form->id}", [
                'title' => $form->title,
                'captcha_enabled' => false,
            ])
            ->assertOk();

        $this->assertDatabaseHas('forms', ['id' => $form->id, 'captcha_enabled' => false]);
    }

    public function test_edit_page_receives_captcha_enabled_flag(): void
    {
        $form = (new FormModelFactory)->create(['captcha_enabled' => true]);

        $this->actingAs($this->admin)
            ->withoutMiddleware(AdminMiddleware::class)
            ->get("/admin/forms/{$form->id}/edit")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('FormBuilder/Edit', false)
                ->where('form.captcha_enabled', true)
            );
    }
}
