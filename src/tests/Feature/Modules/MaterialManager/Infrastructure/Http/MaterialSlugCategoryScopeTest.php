<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\MaterialManager\Infrastructure\Http;

use App\Modules\CategoryManager\Infrastructure\Models\CategoryModel;
use App\Modules\UserManager\Infrastructure\Http\Middleware\AdminMiddleware;
use App\Modules\UserManager\Infrastructure\Models\UserModel as User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Feature\Modules\MaterialManager\Infrastructure\Models\MaterialModelFactory;
use Tests\TestCase;

class MaterialSlugCategoryScopeTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
        Gate::before(fn (User $user) => $user->id === $this->admin->id ? true : null);
    }

    /** @return array<class-string> */
    private function adminMiddleware(): array
    {
        return [AdminMiddleware::class];
    }

    public function test_same_slug_allowed_in_different_categories(): void
    {
        $catA = CategoryModel::factory()->create(['alias' => 'otchety-2023']);
        $catB = CategoryModel::factory()->create(['alias' => 'otchety-2024']);

        $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->postJson(route('admin.materials.store'), [
                'title' => '2023 год',
                'slug' => '2023-god',
                'category_id' => $catA->id,
            ])
            ->assertCreated();

        $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->postJson(route('admin.materials.store'), [
                'title' => '2023 год',
                'slug' => '2023-god',
                'category_id' => $catB->id,
            ])
            ->assertCreated();

        $this->assertDatabaseHas('materials', ['category_id' => $catA->id, 'slug' => '2023-god']);
        $this->assertDatabaseHas('materials', ['category_id' => $catB->id, 'slug' => '2023-god']);
    }

    public function test_same_slug_rejected_in_same_category(): void
    {
        $cat = CategoryModel::factory()->create();
        (new MaterialModelFactory)->create(['category_id' => $cat->id, 'slug' => '2023-god']);

        $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->postJson(route('admin.materials.store'), [
                'title' => '2023 год',
                'slug' => '2023-god',
                'category_id' => $cat->id,
            ])
            ->assertUnprocessable()
            ->assertJsonPath('errors.slug.0', 'В этой категории уже есть материал с таким адресом');
    }

    public function test_same_slug_rejected_for_root_materials(): void
    {
        (new MaterialModelFactory)->create(['category_id' => null, 'slug' => 'kontakty']);

        $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->postJson(route('admin.materials.store'), [
                'title' => 'Контакты',
                'slug' => 'kontakty',
            ])
            ->assertUnprocessable()
            ->assertJsonPath('errors.slug.0', 'Материал с таким адресом уже существует');
    }

    public function test_update_slug_to_taken_in_same_category_rejected(): void
    {
        $cat = CategoryModel::factory()->create();
        $material = (new MaterialModelFactory)->create([
            'category_id' => $cat->id,
            'slug' => '2023-god',
            'user_id' => $this->admin->id,
        ]);
        (new MaterialModelFactory)->create(['category_id' => $cat->id, 'slug' => '2024-god']);

        $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->putJson(route('admin.materials.update', $material->id), ['slug' => '2024-god'])
            ->assertUnprocessable()
            ->assertJsonPath('errors.slug.0', 'В этой категории уже есть материал с таким адресом');
    }

    public function test_update_slug_to_taken_in_other_category_allowed(): void
    {
        $catA = CategoryModel::factory()->create();
        $catB = CategoryModel::factory()->create();
        $material = (new MaterialModelFactory)->create([
            'category_id' => $catA->id,
            'slug' => '2023-god',
            'user_id' => $this->admin->id,
        ]);
        (new MaterialModelFactory)->create(['category_id' => $catB->id, 'slug' => '2024-god']);

        $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->putJson(route('admin.materials.update', $material->id), ['slug' => '2024-god'])
            ->assertOk();

        $this->assertDatabaseHas('materials', ['id' => $material->id, 'slug' => '2024-god']);
    }

    public function test_same_slug_urls_in_different_categories_render_own_materials(): void
    {
        $catA = CategoryModel::factory()->create(['alias' => 'otchety-2023']);
        $catB = CategoryModel::factory()->create(['alias' => 'otchety-2024']);
        $a = (new MaterialModelFactory)->create([
            'state' => 'published',
            'published_at' => now()->subDay(),
            'access' => 'public',
            'category_id' => $catA->id,
            'slug' => '2023-god',
        ]);
        $b = (new MaterialModelFactory)->create([
            'state' => 'published',
            'published_at' => now()->subDay(),
            'access' => 'public',
            'category_id' => $catB->id,
            'slug' => '2023-god',
        ]);

        $this->get('/otchety-2023/2023-god')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('MaterialManager/Show', false)
                ->where('material.id', $a->id)
            );

        $this->get('/otchety-2024/2023-god')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('MaterialManager/Show', false)
                ->where('material.id', $b->id)
            );
    }

    public function test_root_slug_renders_root_material_when_categorized_duplicate_exists(): void
    {
        $cat = CategoryModel::factory()->create(['alias' => 'novosti']);
        $root = (new MaterialModelFactory)->create([
            'state' => 'published',
            'published_at' => now()->subDay(),
            'access' => 'public',
            'category_id' => null,
            'slug' => '2023-god',
        ]);
        (new MaterialModelFactory)->create([
            'state' => 'published',
            'published_at' => now()->subDay(),
            'access' => 'public',
            'category_id' => $cat->id,
            'slug' => '2023-god',
        ]);

        $this->get('/2023-god')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('MaterialManager/Show', false)
                ->where('material.id', $root->id)
            );
    }
}
