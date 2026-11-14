<?php

namespace App\Modules\MaterialManager\Domain\Services;

interface CategoryServiceInterface
{
    public function findById(int $id): ?object;

    public function findBySlug(string $slug): ?object;

    public function getAll(): array;

    /**
     * Дочерние категории с хотя бы одним опубликованным материалом,
     * доступным при заданных уровнях доступа (для публичной страницы категории).
     * Пустые (без опубликованных материалов) не возвращаются.
     *
     * @param int $parentId id родительской категории
     * @param string[] $accessLevels значения MaterialAccess, разрешённые посетителю
     * @return array<int, array{id: int, name: string, alias: string, description: ?string}>
     */
    public function findVisibleChildrenWithMaterials(int $parentId, array $accessLevels): array;
}
