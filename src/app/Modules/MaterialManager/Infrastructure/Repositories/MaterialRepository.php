<?php

namespace App\Modules\MaterialManager\Infrastructure\Repositories;

use App\Modules\MaterialManager\Application\DTO\PaginatedResult;
use App\Modules\MaterialManager\Domain\Entities\Material;
use App\Modules\MaterialManager\Domain\Repositories\MaterialRepositoryInterface;
use App\Modules\MaterialManager\Infrastructure\Models\MaterialModel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class MaterialRepository implements MaterialRepositoryInterface
{
    private function cacheEnabled(): bool
    {
        return (bool) Config::get('material-manager.cache.enabled', true);
    }

    private function cacheTtl(): int
    {
        return (int) Config::get('material-manager.cache.ttl', 3600);
    }

    private function cachePrefix(): string
    {
        return (string) Config::get('material-manager.cache.key_prefix', 'material_');
    }

    private function cacheKey(string $type, string|int $identifier): string
    {
        return $this->cachePrefix() . "{$type}:{$identifier}";
    }

    private function clearCache(Material $material, ?string $oldSlug = null): void
    {
        if (!$this->cacheEnabled()) {
            return;
        }

        if ($material->id !== null) {
            Cache::forget($this->cacheKey('find', $material->id));
        }

        if ($material->slug !== null && $material->slug !== '') {
            Cache::forget($this->cacheKey('slug', $material->slug));
        }

        if ($oldSlug !== null && $oldSlug !== '' && $oldSlug !== $material->slug) {
            Cache::forget($this->cacheKey('slug', $oldSlug));
        }

        Cache::forget($this->cacheKey('list', 'select'));
    }

    private function clearCacheById(int $id): void
    {
        if (!$this->cacheEnabled()) {
            return;
        }

        Cache::forget($this->cacheKey('find', $id));
        Cache::forget($this->cacheKey('list', 'select'));
    }

    public function paginate(array $filters, int $perPage = 10): PaginatedResult
    {
        $query = MaterialModel::with(['category', 'user'])
            ->where('state', '!=', 'trash');

        if (($filters['search'] ?? null) !== null && $filters['search'] !== '') {
            $search = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($search) {
                $q->whereRaw('LOWER(title) LIKE LOWER(?)', [$search])
                    ->orWhereRaw('LOWER(slug) LIKE LOWER(?)', [$search]);
            });
        }

        if (($filters['state'] ?? null) !== null && $filters['state'] !== '') {
            $query->where('state', $filters['state']);
        }

        if (($filters['category_id'] ?? null) !== null) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['access_levels'])) {
            $query->whereIn('access', $filters['access_levels']);
        } elseif (($filters['access'] ?? null) !== null && $filters['access'] !== '') {
            $query->where('access', $filters['access']);
        }

        if (($filters['author'] ?? null) !== null) {
            $query->where('user_id', $filters['author']);
        }

        $sort = $filters['sort'] ?? 'id';
        $direction = strtolower($filters['direction'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['id', 'title', 'views', 'created_at'];
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'id';
        }
        $query->orderBy($sort, $direction);

        $paginated = $query->paginate($perPage);

        $items = $paginated->getCollection()->map(fn ($model) => $model->toDomain())->all();

        return new PaginatedResult(
            items: $items,
            total: (int) $paginated->total(),
            perPage: (int) $paginated->perPage(),
            currentPage: (int) $paginated->currentPage(),
            lastPage: (int) $paginated->lastPage(),
            from: $paginated->firstItem(),
            to: $paginated->lastItem(),
        );
    }

    public function getTrashPaginated(int $perPage = 10): PaginatedResult
    {
        $paginated = MaterialModel::with(['category', 'user'])
            ->where('state', 'trash')
            ->orderBy('id', 'desc')
            ->paginate($perPage);

        $items = $paginated->getCollection()->map(fn ($model) => $model->toDomain())->all();

        return new PaginatedResult(
            items: $items,
            total: (int) $paginated->total(),
            perPage: (int) $paginated->perPage(),
            currentPage: (int) $paginated->currentPage(),
            lastPage: (int) $paginated->lastPage(),
            from: $paginated->firstItem(),
            to: $paginated->lastItem(),
        );
    }

    public function getAllTrashIds(): array
    {
        return MaterialModel::where('state', 'trash')->pluck('id')->all();
    }

    public function findById(int $id): ?Material
    {
        if (!$this->cacheEnabled()) {
            $model = MaterialModel::with(['category', 'user'])->find($id);
            return $model ? $model->toDomain() : null;
        }

        return Cache::remember(
            $this->cacheKey('find', $id),
            $this->cacheTtl(),
            function () use ($id) {
                $model = MaterialModel::with(['category', 'user'])->find($id);
                return $model ? $model->toDomain() : null;
            }
        );
    }

    public function findByIdWithLock(int $id): ?Material
    {
        $model = MaterialModel::with(['category', 'user'])->lockForUpdate()->find($id);
        return $model ? $model->toDomain() : null;
    }

    public function findMany(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        $models = MaterialModel::with(['category', 'user'])
            ->whereIn('id', $ids)
            ->get();

        return $models->map(fn ($model) => $model->toDomain())->all();
    }

    public function findBySlug(string $slug): ?Material
    {
        $model = MaterialModel::where('slug', $slug)
            ->where('state', 'published')
            ->where('published_at', '<=', now())
            ->with(['category', 'user'])
            ->first();

        return $model ? $model->toDomain() : null;
    }

    public function findHomepage(): ?Material
    {
        $model = MaterialModel::where('show_on_homepage', true)
            ->where('state', 'published')
            ->where('published_at', '<=', now())
            ->with(['category', 'user'])
            ->first();

        return $model ? $model->toDomain() : null;
    }

    public function existsBySlug(string $slug, ?int $excludeId = null): bool
    {
        $query = MaterialModel::where('slug', $slug);

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function existsBySlugWithLock(string $slug, ?int $excludeId = null): bool
    {
        $query = MaterialModel::where('slug', $slug)->lockForUpdate();

        if ($excludeId !== null) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }

    public function findForEdit(int $id): ?Material
    {
        $model = MaterialModel::with(['category', 'user'])->find($id);
        return $model ? $model->toDomain() : null;
    }

    public function save(Material $material): Material
    {
        $model = null;
        $oldSlug = null;

        if ($material->id !== null) {
            $model = MaterialModel::find($material->id);

            if ($model === null) {
                throw new \DomainException('Material model not found for update');
            }

            $oldSlug = $model->slug;
        }

        $mapped = MaterialModel::fromDomain($material, $model);
        $mapped->save();

        $this->clearCache($material, $oldSlug);

        $fresh = $mapped->fresh(['category', 'user']);

        return $fresh->toDomain();
    }

    public function delete(Material $material): void
    {
        MaterialModel::where('id', $material->id)->update([
            'state' => 'trash',
            'show_on_homepage' => false,
            'updated_at' => now(),
            'deleted_at' => now(),
        ]);

        $this->clearCache($material);
    }

    public function bulkDelete(array $ids): int
    {
        $count = MaterialModel::whereIn('id', $ids)
            ->where('state', '!=', 'trash')
            ->update([
                'state' => 'trash',
                'show_on_homepage' => false,
                'updated_at' => now(),
                'deleted_at' => now(),
            ]);

        foreach ($ids as $id) {
            $this->clearCacheById($id);
        }

        return $count;
    }

    public function forceDelete(Material $material): void
    {
        $model = MaterialModel::find($material->id);

        if ($model !== null) {
            $model->delete();
        }

        $this->clearCache($material);
    }

    public function bulkForceDelete(array $ids): int
    {
        $count = MaterialModel::whereIn('id', $ids)
            ->where('state', 'trash')
            ->delete();

        foreach ($ids as $id) {
            $this->clearCacheById($id);
        }

        return $count;
    }

    public function restore(Material $material): Material
    {
        $model = MaterialModel::find($material->id);

        if ($model === null) {
            throw new \DomainException('Material model not found for restore');
        }

        $mapped = MaterialModel::fromDomain($material, $model);
        $mapped->save();

        $this->clearCache($material);

        return $model->fresh(['category', 'user'])->toDomain();
    }

    public function bulkRestore(array $ids): int
    {
        $count = MaterialModel::whereIn('id', $ids)
            ->where('state', 'trash')
            ->update([
                'state' => 'draft',
                'deleted_at' => null,
                'updated_at' => now(),
            ]);

        foreach ($ids as $id) {
            $this->clearCacheById($id);
        }

        return $count;
    }

    public function incrementViews(Material $material): void
    {
        MaterialModel::where('id', $material->id)->increment('views');
    }

    public function clearHomepageExcept(int $excludeId): void
    {
        MaterialModel::where('id', '!=', $excludeId)
            ->where('show_on_homepage', true)
            ->update(['show_on_homepage' => false]);
    }

    public function lockHomepageRows(): void
    {
        MaterialModel::where('show_on_homepage', true)->lockForUpdate()->get();
    }

    public function getListForSelect(): array
    {
        if (!$this->cacheEnabled()) {
            return $this->fetchListForSelect();
        }

        return Cache::remember(
            $this->cacheKey('list', 'select'),
            $this->cacheTtl(),
            fn () => $this->fetchListForSelect()
        );
    }

    private function fetchListForSelect(): array
    {
        return MaterialModel::select('id', 'title', 'category_id', 'slug')
            ->get()
            ->toArray();
    }

    public function countInTrash(): int
    {
        return MaterialModel::where('state', 'trash')->count();
    }

    public function bulkPublish(array $ids): int
    {
        $count = MaterialModel::whereIn('id', $ids)
            ->whereIn('state', ['draft', 'archived'])
            ->update([
                'state' => 'published',
                'published_at' => now(),
                'updated_at' => now(),
            ]);

        foreach ($ids as $id) {
            $this->clearCacheById($id);
        }

        return $count;
    }

    public function bulkUnpublish(array $ids): int
    {
        $count = MaterialModel::whereIn('id', $ids)
            ->where('state', 'published')
            ->update([
                'state' => 'draft',
                'published_at' => null,
                'updated_at' => now(),
            ]);

        foreach ($ids as $id) {
            $this->clearCacheById($id);
        }

        return $count;
    }
}
