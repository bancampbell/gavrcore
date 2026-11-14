<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\MaterialManager\Infrastructure\Http\Controllers;

use App\Modules\CategoryManager\Infrastructure\Models\CategoryModel;
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
                ->component('Site/Home', false)
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

        $this->get('/'.$material->slug)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('MaterialManager/Show', false)
                ->where('material.id', $material->id)
            );
    }

    public function test_material_with_category_renders_at_category_slug_url(): void
    {
        $category = CategoryModel::factory()->create(['alias' => 'novosti']);
        $material = (new MaterialModelFactory)->create([
            'state' => 'published',
            'published_at' => now()->subDay(),
            'access' => 'public',
            'category_id' => $category->id,
            'slug' => 'strategiia-razvitiia',
        ]);

        $this->get('/novosti/strategiia-razvitiia')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('MaterialManager/Show', false)
                ->where('material.id', $material->id)
            );
    }

    public function test_public_material_json_exposes_category_alias_not_slug(): void
    {
        // TECHDEBT-43: CategorySummaryVO переименован slug → alias — публичный JSON
        // материала обязан отдавать category.alias и не содержать category.slug.
        $category = CategoryModel::factory()->create(['alias' => 'novosti', 'name' => 'Новости']);
        (new MaterialModelFactory)->create([
            'state' => 'published',
            'published_at' => now()->subDay(),
            'access' => 'public',
            'category_id' => $category->id,
            'slug' => 'strategiia-razvitiia',
        ]);

        $this->get('/novosti/strategiia-razvitiia')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('MaterialManager/Show', false)
                ->where('material.category.id', $category->id)
                ->where('material.category.name', 'Новости')
                ->where('material.category.alias', 'novosti')
                ->missing('material.category.slug')
            );
    }

    public function test_public_material_json_category_is_null_without_category(): void
    {
        (new MaterialModelFactory)->create([
            'state' => 'published',
            'published_at' => now()->subDay(),
            'access' => 'public',
            'category_id' => null,
            'slug' => 'contacts',
        ]);

        $this->get('/contacts')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('MaterialManager/Show', false)
                ->where('material.category', null)
            );
    }

    public function test_category_slug_url_returns_404_when_category_mismatch(): void
    {
        $category = CategoryModel::factory()->create(['alias' => 'novosti']);
        $otherCategory = CategoryModel::factory()->create(['alias' => 'dokumenty']);
        (new MaterialModelFactory)->create([
            'state' => 'published',
            'published_at' => now()->subDay(),
            'access' => 'public',
            'category_id' => $category->id,
            'slug' => 'strategiia-razvitiia',
        ]);

        $this->get("/{$otherCategory->alias}/strategiia-razvitiia")->assertNotFound();
    }

    public function test_root_slug_renders_material_without_category(): void
    {
        $material = (new MaterialModelFactory)->create([
            'state' => 'published',
            'published_at' => now()->subDay(),
            'access' => 'public',
            'category_id' => null,
            'slug' => 'contacts',
        ]);

        $this->get('/contacts')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('MaterialManager/Show', false)
                ->where('material.id', $material->id)
            );
    }

    public function test_root_slug_returns_404_when_material_has_category(): void
    {
        $category = CategoryModel::factory()->create(['alias' => 'novosti']);
        (new MaterialModelFactory)->create([
            'state' => 'published',
            'published_at' => now()->subDay(),
            'access' => 'public',
            'category_id' => $category->id,
            'slug' => 'strategiia-razvitiia',
        ]);

        $this->get('/strategiia-razvitiia')->assertNotFound();
    }

    public function test_legacy_material_url_redirects_to_category_slug(): void
    {
        $category = CategoryModel::factory()->create(['alias' => 'novosti']);
        (new MaterialModelFactory)->create([
            'state' => 'published',
            'published_at' => now()->subDay(),
            'access' => 'public',
            'category_id' => $category->id,
            'slug' => 'strategiia-razvitiia',
        ]);

        $this->get('/material/strategiia-razvitiia')
            ->assertStatus(301)
            ->assertRedirect('/novosti/strategiia-razvitiia');
    }

    public function test_legacy_material_url_redirects_to_root_slug_without_category(): void
    {
        (new MaterialModelFactory)->create([
            'state' => 'published',
            'published_at' => now()->subDay(),
            'access' => 'public',
            'category_id' => null,
            'slug' => 'contacts',
        ]);

        $this->get('/material/contacts')
            ->assertStatus(301)
            ->assertRedirect('/contacts');
    }

    public function test_show_returns_403_for_registered_material_when_guest(): void
    {
        $material = (new MaterialModelFactory)->create([
            'state' => 'published',
            'published_at' => now()->subDay(),
            'access' => 'registered',
        ]);

        $this->get('/'.$material->slug)->assertForbidden();
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
                ->component('Site/Search/Index', false)
                ->has('materials.data')
            );
    }

    public function test_seeded_demo_material_renders_form_shortcode(): void
    {
        // AUDIT-22: MaterialSeeder создаёт демо-страницу с шорткодом [form id]
        User::factory()->count(2)->create();
        $this->seed([CategorySeeder::class, FormsSeeder::class, MaterialSeeder::class]);

        $form = FormModel::where('alias', 'feedback')->firstOrFail();

        // Демо-материал привязан к первой категории сидера (Uncategorized)
        $this->get('/uncategorized/obratnaia-sviaz')
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

    public function test_category_page_lists_only_children_with_published_materials(): void
    {
        $parent = CategoryModel::factory()->create(['alias' => 'raskrytie', 'is_active' => true]);
        $childWith = CategoryModel::factory()->create([
            'alias' => 'otchety', 'name' => 'ОТЧЕТЫ', 'parent_id' => $parent->id, 'is_active' => true,
        ]);
        $childEmpty = CategoryModel::factory()->create([
            'alias' => 'pusto', 'parent_id' => $parent->id, 'is_active' => true,
        ]);

        (new MaterialModelFactory)->create([
            'state' => 'published',
            'published_at' => now()->subDay(),
            'access' => 'public',
            'category_id' => $childWith->id,
            'slug' => '2026-god',
        ]);
        (new MaterialModelFactory)->create([
            'state' => 'draft',
            'access' => 'public',
            'category_id' => $childEmpty->id,
            'slug' => 'chernovik',
        ]);

        $this->get('/category/raskrytie')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Site/Category/Show', false)
                ->has('children', 1)
                ->where('children.0.alias', 'otchety')
                ->where('children.0.name', 'ОТЧЕТЫ')
            );
    }


}
