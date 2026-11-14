<?php

namespace App\Console\Commands;

use App\Modules\CategoryManager\Application\DTO\CategoryData;
use App\Modules\CategoryManager\Application\UseCases\CreateCategoryUseCase;
use App\Modules\CategoryManager\Domain\Repositories\CategoryRepositoryInterface;
use App\Modules\CategoryManager\Domain\ValueObjects\CategoryAlias;
use App\Modules\MaterialManager\Application\DTO\CreateMaterialData;
use App\Modules\MaterialManager\Application\UseCases\CreateMaterialUseCase;
use App\Modules\MaterialManager\Domain\Repositories\MaterialRepositoryInterface;
use App\Modules\MaterialManager\Domain\ValueObjects\MaterialAccess;
use App\Modules\MaterialManager\Domain\ValueObjects\MaterialStatus;
use App\Modules\MenuManager\Application\DTO\CreateMenuItemData;
use App\Modules\MenuManager\Application\DTO\CreateMenuTypeData;
use App\Modules\MenuManager\Application\UseCases\CreateMenuItemUseCase;
use App\Modules\MenuManager\Application\UseCases\CreateMenuTypeUseCase;
use App\Modules\MenuManager\Application\UseCases\DeleteMenuItemUseCase;
use App\Modules\MenuManager\Domain\Repositories\MenuItemRepositoryInterface;
use App\Modules\MenuManager\Domain\Repositories\MenuTypeRepositoryInterface;
use App\Modules\MenuManager\Domain\ValueObjects\MenuAlias;
use App\Modules\UserManager\Infrastructure\Models\UserModel;
use Illuminate\Console\Command;
use RuntimeException;

/**
 * Одноразовый перенос структуры сайта с Joomla 3 (srednyayavolga.ru) в GavrCore:
 * категории → пустые материалы-заглушки → меню main-menu с деревом пунктов.
 * Контент не переносится. Alias'и сохраняются из JSON для будущих редиректов/SEO.
 *
 * Создание идёт только через публичные UseCase'ы модулей, чтобы отработали
 * валидация, доменные события и audit (spatie-activitylog). Идемпотентна:
 * существующие записи (по alias/slug) не дублируются, а используются для
 * привязок; пункты меню создаются только если main-menu пусто (или --force).
 */
class SeedJoomlaStructureCommand extends Command
{
    protected $signature = 'app:seed-joomla-structure
        {--file= : Путь к JSON со структурой (по умолчанию srednyaya-volga-structure.json в корне проекта)}
        {--force : Пересоздать пункты меню main-menu, если они уже существуют}';

    protected $description = 'Перенос структуры сайта с Joomla 3 (категории, материалы-заглушки, меню) из JSON';

    /**
     * Зеркало RESERVED_SLUGS из CreateMaterialRequest: slug материала не может
     * совпадать с системными адресами. Список приватен в реквесте, дублируем
     * здесь, чтобы консоль проходил ту же валидацию, что и админка.
     */
    private const RESERVED_SLUGS = [
        'admin', 'api', 'search', 'sitemap', 'storage', 'material', 'category',
        'login', 'register', 'dashboard', 'galleries',
    ];

    /** Лимит колонок alias/slug (string(255)) и правил max:255 Form Request'ов. */
    private const ALIAS_MAX_LENGTH = 255;

    /** @var array{cat_created: int, cat_skipped: int, mat_created: int, mat_skipped: int, mat_failed: int, mi_created: int, mi_skipped: int} */
    private array $stats = [
        'cat_created' => 0, 'cat_skipped' => 0,
        'mat_created' => 0, 'mat_skipped' => 0, 'mat_failed' => 0,
        'mi_created' => 0, 'mi_skipped' => 0,
    ];

    public function handle(
        CreateCategoryUseCase $createCategory,
        CategoryRepositoryInterface $categories,
        CreateMaterialUseCase $createMaterial,
        MaterialRepositoryInterface $materials,
        CreateMenuTypeUseCase $createMenuType,
        MenuTypeRepositoryInterface $menuTypes,
        CreateMenuItemUseCase $createMenuItem,
        DeleteMenuItemUseCase $deleteMenuItem,
        MenuItemRepositoryInterface $menuItems,
    ): int {
        $path = $this->option('file') ?: base_path('srednyaya-volga-structure.json');

        try {
            $structure = $this->loadStructure($path);
        } catch (RuntimeException $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info('Перенос структуры Joomla 3 → GavrCore');
        $this->line("Источник: {$path}");

        // CreateMaterialUseCase требует автора (userId материала + actorId
        // для MaterialCreated → audit). Берём первого пользователя — на dev/stage
        // это администратор; ручные правки перезаписи не подлежат.
        $actor = UserModel::query()->orderBy('id')->first();

        if ($actor === null) {
            $this->error('В системе нет ни одного пользователя — материалы создать невозможно (нужен автор). Запустите сначала gavr:install.');

            return self::FAILURE;
        }

        $categoryMap = $this->seedCategories($structure['categories'], $categories, $createCategory);
        $materialMap = $this->seedMaterials($structure['materials'], $categoryMap['byTitle'], $materials, $createMaterial, $actor->id);
        $menuResult = $this->seedMenu($structure['menu'], $categoryMap['byAlias'], $materialMap, $menuTypes, $createMenuType, $menuItems, $createMenuItem, $deleteMenuItem);

        $this->newLine();
        $this->info('Перенос завершён.');
        $this->table(
            ['Сущность', 'Создано', 'Пропущено (уже были)'],
            [
                ['Категории', $this->stats['cat_created'], $this->stats['cat_skipped']],
                ['Материалы', $this->stats['mat_created'], $this->stats['mat_skipped']],
                ['Пункты меню', $this->stats['mi_created'], $this->stats['mi_skipped']],
            ]
        );

        if ($this->stats['mat_failed'] > 0) {
            $this->warn("Материалов с ошибками: {$this->stats['mat_failed']} (см. предупреждения выше).");
        }

        $this->line("Меню: «{$menuResult['title']}» (alias: {$menuResult['alias']}), id={$menuResult['id']}");

        return self::SUCCESS;
    }

    /**
     * @return array{source?: string, categories: array<int, array{title: string, alias: string}>, materials: array<int, array{title: string, alias: string, category: ?string, access: string}>, menu: array{title: string, alias: string, items: array<int, array<string, mixed>>}}
     */
    private function loadStructure(string $path): array
    {
        if (! is_file($path)) {
            throw new RuntimeException("Файл структуры не найден: {$path}");
        }

        $decoded = json_decode((string) file_get_contents($path), true);

        if (! is_array($decoded)
            || ! isset($decoded['categories'], $decoded['materials'], $decoded['menu'])
            || ! is_array($decoded['categories'])
            || ! is_array($decoded['materials'])
            || ! is_array($decoded['menu'])
        ) {
            throw new RuntimeException("Файл {$path} не похож на экспорт структуры (нужны ключи categories, materials, menu).");
        }

        return $decoded;
    }

    /**
     * Категории: идемпотентно по alias — существующие (в т.ч. ручные) не
     * дублируются, а мапятся для привязок материалов и меню.
     *
     * @param  array<int, array{title: string, alias: string}>  $items
     * @return array{byAlias: array<string, array{id: int, alias: string}>, byTitle: array<string, array{id: int, alias: string}>}
     *                                                                                                                             byAlias — привязки меню (category_alias), byTitle — привязки материалов (category — имя категории)
     */
    private function seedCategories(array $items, CategoryRepositoryInterface $repository, CreateCategoryUseCase $createCategory): array
    {
        $existing = $this->indexCategoriesByAlias($repository->getAllAsTree());
        $map = ['byAlias' => [], 'byTitle' => []];

        foreach ($items as $item) {
            $originalAlias = trim($item['alias']);
            // Валидация через VO модуля (CategoryAlias) + лимит колонки string(255):
            // правила CreateCategoryRequest max для alias не задаёт, ограничитель — схема БД.
            $vo = new CategoryAlias($originalAlias);
            $alias = $this->sanitizeAlias($vo->value);

            if (isset($existing[$alias])) {
                $entry = ['id' => (int) $existing[$alias]['id'], 'alias' => $alias];
                $map['byAlias'][$originalAlias] = $entry;
                $map['byTitle'][trim($item['title'])] = $entry;
                $this->stats['cat_skipped']++;

                continue;
            }

            // Только fromArray: у CategoryData toArray() отдаёт поля, переданные
            // через fromArray (hasField-семантика), конструктор напрямую дал бы пустой массив.
            $model = $createCategory->execute(CategoryData::fromArray([
                'name' => trim($item['title']),
                'alias' => $alias,
                'is_active' => true,
            ]));

            $entry = ['id' => (int) $model->id, 'alias' => (string) $model->alias];
            $map['byAlias'][$originalAlias] = $entry;
            $map['byTitle'][trim($item['title'])] = $entry;
            $existing[(string) $model->alias] = ['id' => (int) $model->id];
            $this->stats['cat_created']++;
        }

        return $map;
    }

    /**
     * @param  array<int, array<string, mixed>>  $tree
     * @return array<string, array{id: int}>
     */
    private function indexCategoriesByAlias(array $tree): array
    {
        $index = [];

        foreach ($tree as $node) {
            $index[(string) $node['alias']] = ['id' => (int) $node['id']];
            foreach ($this->indexCategoriesByAlias($node['children'] ?? []) as $alias => $data) {
                $index[$alias] = $data;
            }
        }

        return $index;
    }

    /**
     * Материалы-заглушки: пустой контент, опубликованы, доступ public.
     * JSON alias → slug материала (у MaterialManager нет VO для alias, зеркало
     * правил — CreateMaterialRequest: max:255, notIn RESERVED_SLUGS, уникален
     * в рамках категории). Идемпотентность — existsBySlug с scope категории.
     *
     * @param  array<int, array{title: string, alias: string, category: ?string, access: string}>  $items
     * @param  array<string, array{id: int, alias: string}>  $categoryMap  мапа имя категории → категория
     * @return array<string, string> мапа исходный alias JSON → финальный slug материала
     */
    private function seedMaterials(array $items, array $categoryMap, MaterialRepositoryInterface $repository, CreateMaterialUseCase $createMaterial, int $actorId): array
    {
        $map = [];

        foreach ($items as $item) {
            $originalAlias = trim($item['alias']);
            $categoryName = $item['category'] ?? null;
            $categoryId = null;

            if ($categoryName !== null) {
                if (! isset($categoryMap[$categoryName])) {
                    $this->warn("Материал «{$item['title']}» пропущен: категория «{$categoryName}» не найдена.");
                    $this->stats['mat_failed']++;

                    continue;
                }
                $categoryId = $categoryMap[$categoryName]['id'];
            }

            $slug = $this->sanitizeAlias($originalAlias, true);

            if ($repository->existsBySlug($slug, null, $categoryId)) {
                $map[$originalAlias] = $slug;
                $this->stats['mat_skipped']++;

                continue;
            }

            try {
                $material = $createMaterial->execute(
                    new CreateMaterialData(
                        title: trim($item['title']),
                        slug: $slug,
                        content: '',
                        categoryId: $categoryId,
                        userId: $actorId,
                        status: MaterialStatus::PUBLISHED,
                        access: MaterialAccess::PUBLIC,
                        showOnHomepage: false,
                        showDate: true,
                        showAuthor: true,
                        showCategory: true,
                        showViews: true,
                        useGlobalSettings: true,
                        metaTitle: null,
                        metaDescription: null,
                        metaKeywords: null,
                    ),
                    $actorId
                );
                $map[$originalAlias] = $material->slug;
                $this->stats['mat_created']++;
            } catch (\DomainException $e) {
                // Конфликт slug на частичном unique-индексе (category_id, slug) —
                // не должен случаться при предварительном existsBySlug, но прогон
                // прерывать не стоит.
                $this->warn("Материал «{$item['title']}» не создан: {$e->getMessage()}");
                $this->stats['mat_failed']++;
            }
        }

        return $map;
    }

    /**
     * Меню main-menu и дерево пунктов (до 2 уровней вложенности).
     * Если меню уже существует и содержит пункты — предупреждение и пропуск
     * (или --force: корневые пункты удаляются через публичный UC, дети —
     * каскадом по FK модуля, затем пункты пересоздаются из JSON).
     *
     * @param  array{title: string, alias: string, items: array<int, array<string, mixed>>}  $menu
     * @param  array<string, array{id: int, alias: string}>  $categoryMap
     * @param  array<string, string>  $materialMap
     * @return array{id: int, title: string, alias: string}
     */
    private function seedMenu(
        array $menu,
        array $categoryMap,
        array $materialMap,
        MenuTypeRepositoryInterface $menuTypes,
        CreateMenuTypeUseCase $createMenuType,
        MenuItemRepositoryInterface $menuItems,
        CreateMenuItemUseCase $createMenuItem,
        DeleteMenuItemUseCase $deleteMenuItem,
    ): array {
        // Валидация через VO модуля + лимит колонки (правила CreateMenuTypeRequest).
        $vo = new MenuAlias(trim($menu['alias']));
        $alias = $this->sanitizeAlias($vo->value());

        $type = $menuTypes->findByAlias($alias);

        if ($type === null) {
            $maxOrdering = $menuTypes->getAll()->reduce(
                fn (int $carry, $menuType): int => max($carry, (int) $menuType->ordering),
                0
            );

            $type = $createMenuType->execute(new CreateMenuTypeData(
                title: trim($menu['title']),
                alias: $alias,
                description: 'Перенесено из Joomla 3 (srednyayavolga.ru)',
                ordering: $maxOrdering + 1,
                status: true,
            ));
            $this->line("Создан тип меню «{$type->title}» (alias: {$type->alias}), id={$type->id}");
        }

        $tree = $menuItems->getTree($type->id);
        $existingCount = $this->countTree($tree);

        if ($existingCount > 0) {
            if (! $this->option('force')) {
                $this->warn("Меню «{$alias}» (id={$type->id}) уже содержит {$existingCount} пунктов — пункты не созданы. Для пересоздания запустите с --force.");
                $this->stats['mi_skipped'] = $existingCount;

                return ['id' => (int) $type->id, 'title' => (string) $type->title, 'alias' => (string) $type->alias];
            }

            foreach ($tree as $root) {
                $deleteMenuItem->execute((int) $root['id']);
            }
            $this->warn("Удалено {$existingCount} существующих пунктов меню «{$alias}» (--force), пересоздаю из JSON.");
        }

        $this->createMenuItems($menu['items'], (int) $type->id, null, $categoryMap, $materialMap, $createMenuItem);

        return ['id' => (int) $type->id, 'title' => (string) $type->title, 'alias' => (string) $type->alias];
    }

    /**
     * Рекурсивное создание пунктов меню из дерева JSON. Ordering назначает
     * CreateMenuItemUseCase (max + 1 в рамках scope), создание в порядке JSON
     * сохраняет сортировку оригинала.
     *
     * @param  array<int, array<string, mixed>>  $items
     * @param  array<string, array{id: int, alias: string}>  $categoryMap
     * @param  array<string, string>  $materialMap
     */
    private function createMenuItems(array $items, int $menuTypeId, ?int $parentId, array $categoryMap, array $materialMap, CreateMenuItemUseCase $createMenuItem): void
    {
        foreach ($items as $item) {
            $link = $this->resolveLink($item, $categoryMap, $materialMap);

            if ($link === null) {
                $this->warn("Пункт меню «{$item['title']}» пропущен: цель ссылки не найдена (см. предупреждения выше).");
                $this->stats['mi_skipped']++;

                continue;
            }

            $model = $createMenuItem->execute(new CreateMenuItemData(
                menuTypeId: $menuTypeId,
                parentId: $parentId,
                title: trim($item['title']),
                linkType: $link['type'],
                linkValue: $link['value'],
                target: '_self',
                status: true,
                access: 'all',
                language: 'all',
                position: null,
                afterId: null,
            ));

            $this->stats['mi_created']++;

            if (! empty($item['children']) && is_array($item['children'])) {
                $this->createMenuItems($item['children'], $menuTypeId, (int) $model->id, $categoryMap, $materialMap, $createMenuItem);
            }
        }
    }

    /**
     * Маппинг типов пунктов Joomla → link_type GavrCore:
     * material → slug материала, category → alias категории, external → url,
     * group (бывший separator Joomla) → некликабельный 'separator'.
     *
     * @param  array<string, mixed>  $item
     * @param  array<string, array{id: int, alias: string}>  $categoryMap
     * @param  array<string, string>  $materialMap
     * @return ?array{type: string, value: ?string} null — цель не найдена, пункт пропускаем
     */
    private function resolveLink(array $item, array $categoryMap, array $materialMap): ?array
    {
        return match ($item['type'] ?? null) {
            'material' => $this->resolveMaterialLink($item, $materialMap),
            'category' => $this->resolveCategoryLink($item, $categoryMap),
            'external' => ['type' => 'external', 'value' => (string) ($item['url'] ?? '')],
            'group' => ['type' => 'separator', 'value' => null],
            default => null,
        };
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  array<string, string>  $materialMap
     * @return ?array{type: string, value: ?string}
     */
    private function resolveMaterialLink(array $item, array $materialMap): ?array
    {
        $alias = $item['material_alias'] ?? null;

        if ($alias === null || ! isset($materialMap[$alias])) {
            $this->warn("Пункт меню «{$item['title']}»: материал с alias «{$alias}» не найден.");

            return null;
        }

        return ['type' => 'material', 'value' => $materialMap[$alias]];
    }

    /**
     * @param  array<string, mixed>  $item
     * @param  array<string, array{id: int, alias: string}>  $categoryMap
     * @return ?array{type: string, value: ?string}
     */
    private function resolveCategoryLink(array $item, array $categoryMap): ?array
    {
        $alias = $item['category_alias'] ?? null;

        if ($alias === null || ! isset($categoryMap[$alias])) {
            $this->warn("Пункт меню «{$item['title']}»: категория с alias «{$alias}» не найдена.");

            return null;
        }

        return ['type' => 'category', 'value' => $categoryMap[$alias]['alias']];
    }

    /**
     * Проверка alias по правилам модулей (через VO — CategoryAlias/MenuAlias —
     * и лимит схемы БД string(255); для материалов также RESERVED_SLUGS).
     * Невалидный alias не роняет прогон: обрезаем и добавляем детерминированный
     * суффикс-хэш от полного значения — повторные прогоны дают тот же slug,
     * а внешний existsBySlug его находит и переиспользует (идемпотентность).
     */
    private function sanitizeAlias(string $alias, bool $checkReserved = false): string
    {
        $problems = [];

        if ($alias === '') {
            $problems[] = 'пустое значение';
        }

        if (mb_strlen($alias) > self::ALIAS_MAX_LENGTH) {
            $problems[] = 'длина '.mb_strlen($alias).' > '.self::ALIAS_MAX_LENGTH;
        }

        if ($checkReserved && in_array($alias, self::RESERVED_SLUGS, true)) {
            $problems[] = 'совпадает с системным адресом';
        }

        if ($problems === []) {
            return $alias;
        }

        // Обрезка с запасом под суффикс: base + '-' + 8 символов хэша ≤ 255.
        $suffix = substr(md5($alias), 0, 8);
        $base = substr($alias, 0, self::ALIAS_MAX_LENGTH - strlen($suffix) - 1);
        $fallback = $base.'-'.$suffix;

        $this->warn("Alias «{$alias}» не прошёл валидацию (".implode('; ', $problems)."). Использован уникальный суффикс: «{$fallback}»");

        return $fallback;
    }

    /**
     * @param  array<int, array<string, mixed>>  $tree
     */
    private function countTree(array $tree): int
    {
        $count = 0;

        foreach ($tree as $node) {
            $count++;
            $count += $this->countTree($node['children'] ?? []);
        }

        return $count;
    }
}
