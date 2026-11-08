<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\MenuManager\Infrastructure\Http\Controllers;

use App\Modules\CategoryManager\Infrastructure\Models\CategoryModel;
use App\Modules\MenuManager\Application\UseCases\GetMenuTreeUseCase;
use App\Modules\MenuManager\Infrastructure\Models\MenuItemModel;
use App\Modules\MenuManager\Infrastructure\Models\MenuTypeModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Feature\Modules\MaterialManager\Infrastructure\Models\MaterialModelFactory;
use Tests\TestCase;

/**
 * Публичное дерево меню (эндпоинт web.menu.tree / shared-prop mainMenu):
 * бэкенд отдаёт готовый url для каждого пункта (TECHDEBT п.28).
 */
class PublicMenuUrlTest extends TestCase
{
    use RefreshDatabase;

    private MenuTypeModel $menuType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->menuType = MenuTypeModel::create([
            'title' => 'Главное меню',
            'alias' => 'main-menu',
            'ordering' => 0,
            'status' => true,
        ]);
    }

    public function test_material_item_with_category_gets_category_url(): void
    {
        $category = CategoryModel::factory()->create(['alias' => 'news', 'slug' => 'news']);
        (new MaterialModelFactory)->create(['slug' => 'hello-world', 'category_id' => $category->id]);
        $this->createItem('Новость', 'material', 'hello-world', 1);

        $tree = $this->getTree();

        $this->assertSame('/news/hello-world', $tree[0]['url']);
    }

    public function test_material_item_without_category_gets_root_url(): void
    {
        (new MaterialModelFactory)->create(['slug' => 'contacts', 'category_id' => null]);
        $this->createItem('Контакты', 'material', 'contacts', 1);

        $tree = $this->getTree();

        $this->assertSame('/contacts', $tree[0]['url']);
    }

    public function test_material_item_pointing_to_missing_material_gets_null_url(): void
    {
        $this->createItem('Битый пункт', 'material', 'no-such-material', 1);

        $tree = $this->getTree();

        $this->assertNull($tree[0]['url']);
    }

    public function test_category_item_gets_category_url(): void
    {
        CategoryModel::factory()->create(['alias' => 'novosti']);
        $this->createItem('Новости', 'category', 'novosti', 1);

        $tree = $this->getTree();

        $this->assertSame('/category/novosti', $tree[0]['url']);
    }

    public function test_url_item_passes_link_value_unchanged(): void
    {
        $this->createItem('О компании', 'url', '/about', 1);

        $tree = $this->getTree();

        $this->assertSame('/about', $tree[0]['url']);
    }

    public function test_separator_and_heading_get_null_url_external_gets_link_value(): void
    {
        $this->createItem('Разделитель', 'separator', null, 1);
        $this->createItem('Заголовок', 'heading', null, 2);
        $this->createItem('Внешняя', 'external', 'https://example.com', 3);

        $tree = $this->getTree();

        $this->assertNull($tree[0]['url']);
        $this->assertNull($tree[1]['url']);
        $this->assertSame('https://example.com', $tree[2]['url']);
    }

    public function test_material_urls_are_resolved_with_single_query(): void
    {
        foreach (['mat-1', 'mat-2', 'mat-3'] as $i => $slug) {
            (new MaterialModelFactory)->create(['slug' => $slug, 'category_id' => null]);
            $this->createItem($slug, 'material', $slug, $i + 1);
        }

        $materialQueries = 0;
        DB::listen(function ($query) use (&$materialQueries) {
            if (preg_match('/from\s+["`]?materials["`]?/i', $query->sql)) {
                $materialQueries++;
            }
        });

        app(GetMenuTreeUseCase::class)->execute('main-menu');

        $this->assertSame(1, $materialQueries);
    }

    private function createItem(string $title, string $linkType, ?string $linkValue, int $ordering): void
    {
        MenuItemModel::create([
            'menu_type_id' => $this->menuType->id,
            'title' => $title,
            'link_type' => $linkType,
            'link_value' => $linkValue,
            'ordering' => $ordering,
            'status' => true,
            'access' => 'all',
            'language' => 'all',
        ]);
    }

    private function getTree(): array
    {
        return $this->get('/menu/main-menu/tree')->assertOk()->json();
    }
}
