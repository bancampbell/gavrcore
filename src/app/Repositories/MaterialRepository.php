<?php

namespace App\Repositories;

use App\Contracts\MaterialRepositoryInterface;
use App\DTO\MaterialData;
use App\Models\Material;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class MaterialRepository implements MaterialRepositoryInterface
{
    public function paginate(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $query = Material::with(['category', 'user'])->where('state', '!=', 'trash');

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'ilike', '%'.$filters['search'].'%')
                    ->orWhere('slug', 'ilike', '%'.$filters['search'].'%');
            });
        }

        if (! empty($filters['state'])) {
            $query->where('state', $filters['state']);
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (! empty($filters['access'])) {
            $query->where('access', $filters['access']);
        }

        if (! empty($filters['author'])) {
            $query->where('user_id', $filters['author']);
        }

        return $query->orderBy('id', 'desc')->paginate($perPage);
    }

    public function find(int $id): ?Material
    {
        return Material::with(['category', 'user'])->find($id);
    }

    public function findBySlug(string $slug): ?Material
    {
        return Material::where('slug', $slug)
            ->where('state', 'published')
            ->with(['category', 'user'])
            ->first();
    }

    public function incrementViews(Material $material): void
    {
        $material->increment('views');
    }

    public function create(MaterialData $data): Material
    {
        return Material::create($data->toArray());
    }

    public function update(Material $material, MaterialData $data): Material
    {
        $material->update($data->toArray());

        return $material->fresh();
    }

    public function delete(Material $material): void
    {
        $material->delete();
    }
}
