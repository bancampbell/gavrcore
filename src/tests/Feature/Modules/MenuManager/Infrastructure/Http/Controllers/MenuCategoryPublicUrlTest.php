<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\MenuManager\Infrastructure\Http\Controllers;

use App\Modules\CategoryManager\Infrastructure\Models\CategoryModel;
use App\Modules\MenuManager\Infrastructure\Models\MenuItemModel;
use App\Modules\MenuManager\Infrastructure\Models\MenuTypeModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Сквозная связка пункт меню 'category' ↔ публичный роут (TECHDEBT п.34):
 * link_value хранит alias категории (НЕ slug — slug nullable и может расходиться
 * с alias), роут /category/{slug} резолвит категорию по alias.
 */
class MenuCategoryPublicUrlTest extends TestCase
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

    public function test_category_menu_item_public_url_renders_category_page_when_alias_differs_from_slug(): void
    {
        CategoryModel::factory()->create(['alias' => 'novosti', 'slug' => 'news']);
        $this->createCategoryItem('novosti');

        // Дерево меню отдаёт ссылку по alias — именно её открывает посетитель
        $tree = $this->get('/menu/main-menu/tree')->assertOk()->json();
        $this->assertSame('/category/novosti', $tree[0]['url']);

        // Публичный URL резолвится по alias даже при расхождении alias/slug
        $this->get('/category/novosti')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Site/Category/Show', false));

        // Обращение по slug не резолвится (контракт — alias)
        $this->get('/category/news')->assertNotFound();
    }

    public function test_category_menu_item_public_url_works_when_category_slug_is_null(): void
    {
        // Фабрика категорий не заполняет slug (NULL) — alias при этом всегда есть
        $category = CategoryModel::factory()->create(['alias' => 'novosti']);
        $this->assertNull($category->slug);
        $this->createCategoryItem('novosti');

        $this->get('/category/novosti')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Site/Category/Show', false));
    }

    private function createCategoryItem(string $alias): void
    {
        MenuItemModel::create([
            'menu_type_id' => $this->menuType->id,
            'title' => 'Новости',
            'link_type' => 'category',
            'link_value' => $alias,
            'ordering' => 1,
            'status' => true,
            'access' => 'all',
            'language' => 'all',
        ]);
    }
}
