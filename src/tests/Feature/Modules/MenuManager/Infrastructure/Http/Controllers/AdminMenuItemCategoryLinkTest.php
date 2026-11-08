<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\MenuManager\Infrastructure\Http\Controllers;

use App\Modules\CategoryManager\Infrastructure\Models\CategoryModel;
use App\Modules\MenuManager\Infrastructure\Models\MenuItemModel;
use App\Modules\MenuManager\Infrastructure\Models\MenuTypeModel;
use App\Modules\UserManager\Infrastructure\Models\PermissionModel;
use App\Modules\UserManager\Infrastructure\Models\UserModel as User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Пункт меню типа 'category' («Список материалов категории»):
 * link_type допускается, link_value обязателен и должен быть существующим alias категории
 * (публичный роут /category/{slug} резолвит категорию по alias — TECHDEBT п.34).
 */
class AdminMenuItemCategoryLinkTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private MenuTypeModel $menuType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->admin->permissions()->attach(
            PermissionModel::create(['name' => 'Доступ к админке', 'key' => 'admin.access', 'group' => 'system'])->id
        );

        $this->menuType = MenuTypeModel::create([
            'title' => 'Главное меню',
            'alias' => 'main-menu',
            'ordering' => 0,
            'status' => true,
        ]);
    }

    public function test_create_menu_item_with_category_link_type_stores_alias(): void
    {
        CategoryModel::factory()->create(['alias' => 'novosti']);

        $response = $this->actingAs($this->admin)->postJson("/admin/menu/types/{$this->menuType->id}/items", [
            'title' => 'Новости',
            'link_type' => 'category',
            'link_value' => 'novosti',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('menu_items', [
            'menu_type_id' => $this->menuType->id,
            'title' => 'Новости',
            'link_type' => 'category',
            'link_value' => 'novosti',
        ]);
    }

    public function test_category_link_type_without_link_value_is_rejected(): void
    {
        $response = $this->actingAs($this->admin)->postJson("/admin/menu/types/{$this->menuType->id}/items", [
            'title' => 'Новости',
            'link_type' => 'category',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['link_value']);
    }

    public function test_category_link_value_must_be_existing_category_alias(): void
    {
        $response = $this->actingAs($this->admin)->postJson("/admin/menu/types/{$this->menuType->id}/items", [
            'title' => 'Новости',
            'link_type' => 'category',
            'link_value' => 'ne-sushchestvuyushchiy-alias',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['link_value']);
    }

    /**
     * slug категории может отличаться от alias (и даже быть NULL) — пункт меню
     * валидируется строго по alias, иначе публичная ссылка отдала бы 404.
     */
    public function test_category_link_value_is_validated_by_alias_not_slug(): void
    {
        CategoryModel::factory()->create(['alias' => 'novosti', 'slug' => 'news']);

        $response = $this->actingAs($this->admin)->postJson("/admin/menu/types/{$this->menuType->id}/items", [
            'title' => 'Новости',
            'link_type' => 'category',
            'link_value' => 'news',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['link_value']);
    }

    public function test_update_menu_item_to_category_link_type_validates_alias(): void
    {
        CategoryModel::factory()->create(['alias' => 'novosti']);
        $item = MenuItemModel::create([
            'menu_type_id' => $this->menuType->id,
            'title' => 'Пункт',
            'link_type' => 'url',
            'link_value' => '/about',
        ]);

        $valid = $this->actingAs($this->admin)->putJson("/admin/menu/items/{$item->id}", [
            'title' => 'Пункт',
            'link_type' => 'category',
            'link_value' => 'novosti',
        ]);
        $valid->assertOk();
        $this->assertDatabaseHas('menu_items', ['id' => $item->id, 'link_type' => 'category', 'link_value' => 'novosti']);

        $invalid = $this->actingAs($this->admin)->putJson("/admin/menu/items/{$item->id}", [
            'title' => 'Пункт',
            'link_type' => 'category',
            'link_value' => 'ne-sushchestvuyushchiy-alias',
        ]);
        $invalid->assertUnprocessable();
        $invalid->assertJsonValidationErrors(['link_value']);
    }
}
