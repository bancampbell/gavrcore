<?php

namespace App\Services;

use App\Contracts\MaterialRepositoryInterface;
use App\DTO\MaterialData;
use App\Models\Material;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class MaterialService
{
    public function __construct(
        protected MaterialRepositoryInterface $repository
    ) {}

    /**
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator<int, Material>
     */
    public function getPaginated(array $filters): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }

    public function create(MaterialData $data): Material
    {
        if (empty($data->alias)) {
            $data = new MaterialData(
                title: $data->title,
                alias: Str::slug($data->title),
                content: $data->content,
                category_id: $data->category_id,
                user_id: $data->user_id,
                state: $data->state,
                access: $data->access,
                published_at: $data->published_at,
            );
        }
        return $this->repository->create($data);
    }

    public function update(Material $material, MaterialData $data): Material
    {
        if (empty($data->alias)) {
            $data = new MaterialData(
                title: $data->title,
                alias: Str::slug($data->title),
                content: $data->content,
                category_id: $data->category_id,
                user_id: $data->user_id,
                state: $data->state,
                access: $data->access,
                published_at: $data->published_at,
            );
        }
        return $this->repository->update($material, $data);
    }

    public function delete(Material $material): void
    {
        $this->repository->delete($material);
    }
}
