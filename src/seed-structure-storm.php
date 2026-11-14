<?php
/**
 * Перенос структуры Joomla 3 (srednyayavolga.ru) — напрямую через Entity Storm.
 * Запуск: php artisan tinker --execute="$(cat seed-structure-storm.php)"
 *   или `php artisan tinker` и вставить целиком.
 * Идемпотентен: существующие alias'ы пропускаются.
 * ВАЖНО: обходит менеджеры (валидация/события/audit мимо) — только для разового сидера.
 */

$data = json_decode(file_get_contents(base_path('srednyaya-volga-structure.json')), true);
//                                             ^^^ JSON положи в корень проекта или поправь путь

/* ====================================================================================
 * [ПРОВЕРИТЬ] Классы Entity Storm и имена полей — правится только этот блок.
 * ================================================================================== */
use App\GavrCore\Categories\CategoryEntity;
use App\GavrCore\Materials\MaterialEntity;
use App\GavrCore\Menu\MenuEntity;
use App\GavrCore\Menu\MenuItemEntity;

// имена полей в БД твоих таблиц:
$F = [
    'cat'  => ['title' => 'title', 'alias' => 'alias', 'parent' => 'parent_id', 'status' => 'status'],
    'mat'  => ['title' => 'title', 'alias' => 'alias', 'cat' => 'category_id', 'body' => 'content', 'status' => 'status'],
    'menu' => ['title' => 'title', 'alias' => 'alias'],
    'item' => ['menu' => 'menu_id', 'parent' => 'parent_id', 'order' => 'order', 'title' => 'title',
               'type' => 'type', 'material' => 'material_id', 'category' => 'category_id', 'url' => 'url'],
];

/* ==== дальше без правок ==== */

// 1. КАТЕГОРИИ
$catIds = [];
foreach ($data['categories'] as $c) {
    $ex = CategoryEntity::where($F['cat']['alias'], $c['alias'])->first();
    if (!$ex) {
        $ex = new CategoryEntity();
        $ex->fill([
            $F['cat']['title']  => $c['title'],
            $F['cat']['alias']  => $c['alias'],
            $F['cat']['parent'] => null,
            $F['cat']['status'] => 'published',
        ])->save();
    }
    $catIds[$c['alias']] = $ex->id;
}

// 2. МАТЕРИАЛЫ-ЗАГЛУШКИ
$matIds = [];
foreach ($data['materials'] as $m) {
    $ex = MaterialEntity::where($F['mat']['alias'], $m['alias'])->first();
    if ($ex) { $matIds[$m['alias']] = $ex->id; continue; }
    $e = new MaterialEntity();
    $e->fill([
        $F['mat']['title']  => $m['title'],
        $F['mat']['alias']  => $m['alias'],
        $F['mat']['cat']    => $m['category'] !== null ? ($catIds[$m['category']] ?? null) : null,
        $F['mat']['body']   => '',
        $F['mat']['status'] => 'published',
    ])->save();
    $matIds[$m['alias']] = $e->id;
}

// 3. МЕНЮ
$menu = MenuEntity::where($F['menu']['alias'], $data['menu']['alias'])->first()
     ?? tap(new MenuEntity(), fn($e) => $e->fill([
            $F['menu']['title'] => $data['menu']['title'],
            $F['menu']['alias'] => $data['menu']['alias'],
        ])->save());

$createItem = function (array $item, $parentId, int $order) use (&$createItem, $menu, $matIds, $catIds, $F) {
    $attrs = [
        $F['item']['menu']   => $menu->id,
        $F['item']['parent'] => $parentId,
        $F['item']['order']  => $order,
        $F['item']['title']  => $item['title'],
        $F['item']['type']   => $item['type'],
    ];
    switch ($item['type']) {
        case 'material': $attrs[$F['item']['material']] = $matIds[$item['material_alias']] ?? null; break;
        case 'category': $attrs[$F['item']['category']] = $catIds[$item['category_alias']] ?? null; break;
        case 'external': $attrs[$F['item']['url']] = $item['url']; break;
        case 'group':    $attrs[$F['item']['url']] = null; break;
    }
    $node = new MenuItemEntity();
    $node->fill($attrs)->save();
    foreach ($item['children'] ?? [] as $i => $child) {
        $createItem($child, $node->id, $i + 1);
    }
};
foreach ($data['menu']['items'] as $i => $item) {
    $createItem($item, null, $i + 1);
}

echo "OK: " . count($catIds) . " категорий, " . count($matIds) . " материалов, меню id={$menu->id}\n";
