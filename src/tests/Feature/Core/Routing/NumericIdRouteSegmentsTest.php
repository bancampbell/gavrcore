<?php

declare(strict_types=1);

namespace Tests\Feature\Core\Routing;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * AUDIT-10: нечисловой сегмент id в роутах должен давать 404, а не 500
 * TypeError «Argument #1 ($id) must be of type int, string given».
 *
 * Проверка ограничений параметров маршрутов: она выполняется на этапе
 * сопоставления URL, поэтому авторизация для 404 не требуется.
 */
class NumericIdRouteSegmentsTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, string> метод + URL */
    public static function nonNumericSegmentsProvider(): array
    {
        return [
            'users edit' => ['get', '/admin/users/abc/edit'],
            'users update' => ['put', '/admin/users/abc'],
            'users destroy' => ['delete', '/admin/users/abc'],
            'groups edit' => ['get', '/admin/groups/abc/edit'],
            'groups update' => ['put', '/admin/groups/abc'],
            'access-levels edit' => ['get', '/admin/access-levels/abc/edit'],
            'materials edit' => ['get', '/admin/materials/abc/edit'],
            'materials update' => ['put', '/admin/materials/abc'],
            'menu types show' => ['get', '/admin/menu/types/abc'],
            'menu types update' => ['put', '/admin/menu/types/abc'],
            'menu items index' => ['get', '/admin/menu/types/abc/items'],
            'menu items tree' => ['get', '/admin/menu/types/abc/items/tree'],
            'menu items create' => ['get', '/admin/menu/types/abc/items/create'],
            'menu items store' => ['post', '/admin/menu/types/abc/items'],
            'menu items show' => ['get', '/admin/menu/items/abc'],
            'menu items edit' => ['get', '/admin/menu/items/abc/edit'],
            'menu items update' => ['put', '/admin/menu/items/abc'],
            'galleries edit' => ['get', '/admin/galleries/abc/edit'],
            'galleries update' => ['put', '/admin/galleries/abc'],
            'galleries images update' => ['put', '/admin/galleries/1/images/abc'],
            'galleries images delete' => ['delete', '/admin/galleries/abc/images/1'],
            'categories show' => ['get', '/admin/categories/abc'],
            'categories update' => ['put', '/admin/categories/abc'],
            'categories destroy' => ['delete', '/admin/categories/abc'],
            'submissions show' => ['get', '/admin/submissions/abc'],
            'submissions destroy' => ['delete', '/admin/submissions/abc'],
        ];
    }

    #[DataProvider('nonNumericSegmentsProvider')]
    public function test_non_numeric_id_segment_returns_404(string $method, string $url): void
    {
        $this->call($method, $url)->assertNotFound();
    }

    public function test_public_gallery_route_rejects_non_numeric_id(): void
    {
        $this->get('/galleries/abc')->assertNotFound();
    }
}
