<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\MaterialManager\Infrastructure\Http\Controllers;

use App\Modules\FormBuilder\Database\Seeders\FormsSeeder;
use App\Modules\FormBuilder\Infrastructure\Models\FormModel;
use App\Modules\SettingManager\Infrastructure\Models\Setting;
use App\Modules\UserManager\Infrastructure\Models\UserModel as User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\MaterialSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Feature\Modules\MaterialManager\Infrastructure\Models\MaterialModelFactory;
use Tests\TestCase;

class WebMaterialControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_shows_homepage_material(): void
    {
        $material = (new MaterialModelFactory)->create([
            'state' => 'published',
            'published_at' => now()->subDay(),
            'access' => 'public',
        ]);

        // AUDIT-13: главный материал назначается настройкой homepage_material_id
        Setting::updateOrCreate(
            ['key' => 'homepage_material_id'],
            ['value' => (string) $material->id]
        );

        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Index', false)
                ->has('homepageMaterial')
                ->where('homepageMaterial.id', $material->id)
            );
    }

    public function test_index_hides_non_public_homepage_for_guests(): void
    {
        $material = (new MaterialModelFactory)->create([
            'state' => 'published',
            'published_at' => now()->subDay(),
            'access' => 'registered',
        ]);

        Setting::updateOrCreate(
            ['key' => 'homepage_material_id'],
            ['value' => (string) $material->id]
        );

        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('homepageMaterial', null)
            );
    }

    public function test_index_ignores_homepage_material_when_type_is_not_material(): void
    {
        $material = (new MaterialModelFactory)->create([
            'state' => 'published',
            'published_at' => now()->subDay(),
            'access' => 'public',
        ]);

        Setting::updateOrCreate(['key' => 'homepage_type'], ['value' => 'default']);
        Setting::updateOrCreate(
            ['key' => 'homepage_material_id'],
            ['value' => (string) $material->id]
        );

        $this->get(route('home'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('homepageMaterial', null)
            );
    }

    public function test_show_displays_public_material(): void
    {
        $material = (new MaterialModelFactory)->create([
            'state' => 'published',
            'published_at' => now()->subDay(),
            'access' => 'public',
        ]);

        $this->get(route('material.show', $material->slug))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('MaterialManager/Show', false)
                ->where('material.id', $material->id)
            );
    }

    public function test_show_returns_403_for_registered_material_when_guest(): void
    {
        $material = (new MaterialModelFactory)->create([
            'state' => 'published',
            'published_at' => now()->subDay(),
            'access' => 'registered',
        ]);

        $this->get(route('material.show', $material->slug))->assertForbidden();
    }

    public function test_search_returns_results(): void
    {
        (new MaterialModelFactory)->create([
            'title' => 'Unique Search Term',
            'state' => 'published',
            'published_at' => now()->subDay(),
            'access' => 'public',
        ]);

        $this->get(route('search', ['q' => 'Unique Search']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Search/Index', false)
                ->has('materials.data')
            );
    }

    public function test_seeded_demo_material_renders_form_shortcode(): void
    {
        // AUDIT-22: MaterialSeeder создаёт демо-страницу с шорткодом [form id]
        User::factory()->count(2)->create();
        $this->seed([CategorySeeder::class, FormsSeeder::class, MaterialSeeder::class]);

        $form = FormModel::where('alias', 'feedback')->firstOrFail();

        $this->get('/material/obratnaia-sviaz')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('MaterialManager/Show', false)
                ->has('forms')
                ->where("forms.{$form->id}.id", $form->id)
            );
    }

    public function test_seeded_demo_form_accepts_submission(): void
    {
        $this->withoutMiddleware(ThrottleRequests::class);

        User::factory()->count(2)->create();
        $this->seed([CategorySeeder::class, FormsSeeder::class, MaterialSeeder::class]);

        $form = FormModel::where('alias', 'feedback')->firstOrFail();

        $response = $this->postJson("/api/forms/{$form->id}/submit", [
            'name' => 'Тест Пользователь',
            'email' => 'test@example.com',
            'message' => 'Проверка демо-формы.',
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Форма успешно отправлена');

        $this->assertDatabaseHas('form_submissions', [
            'form_id' => $form->id,
        ]);
    }
}
