<?php

namespace App\Contracts;

use App\DTO\MaterialData;
use App\Models\Material;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface MaterialRepositoryInterface
{
    /**
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator<int, Material>
     */
    public function paginate(array $filters): LengthAwarePaginator;

    public function find(int $id): ?Material;
    public function create(MaterialData $data): Material;
    public function update(Material $material, MaterialData $data): Material;
    public function delete(Material $material): void;
}
