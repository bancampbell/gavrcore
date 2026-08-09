<?php

namespace App\Modules\MaterialManager\Domain\Repositories;

use App\Modules\MaterialManager\Application\DTO\PaginatedResult;
use App\Modules\MaterialManager\Domain\Entities\Material;

interface MaterialRepositoryInterface
{
    public function paginate(array $filters, int $perPage = 10): PaginatedResult;

    public function getTrashPaginated(int $perPage = 10): PaginatedResult;

    public function getAllTrashIds(): array;

    public function findById(int $id): ?Material;

    public function findByIdWithLock(int $id): ?Material;

    public function findMany(array $ids): array;

    public function findBySlug(string $slug): ?Material;

    // FIX: fetch the material marked for homepage
    public function findHomepage(): ?Material;

    public function existsBySlug(string $slug, ?int $excludeId = null): bool;

    public function existsBySlugWithLock(string $slug, ?int $excludeId = null): bool;

    public function findForEdit(int $id): ?Material;

    public function save(Material $material): Material;

    public function delete(Material $material): void;

    public function bulkDelete(array $ids): int;

    public function forceDelete(Material $material): void;

    public function bulkForceDelete(array $ids): int;

    public function restore(Material $material): Material;

    public function bulkRestore(array $ids): int;

    public function incrementViews(Material $material): void;

    public function clearHomepageExcept(int $excludeId): void;

    public function lockHomepageRows(): void;

    public function getListForSelect(): array;

    public function countInTrash(): int;

    public function bulkPublish(array $ids): int;

    public function bulkUnpublish(array $ids): int;
}
