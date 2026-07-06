<?php

namespace App\Contracts;

use App\DTO\MaterialData;
use App\Models\Material;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface MaterialRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $filters
     *
     * @return LengthAwarePaginator<int, Material>
     */
    public function paginate(array $filters, int $perPage = 10): LengthAwarePaginator;

    public function find(int $id): ?Material;

    public function findBySlug(string $slug): ?Material;

    public function incrementViews(Material $material): void;

    public function create(MaterialData $data): Material;

    public function update(Material $material, MaterialData $data): Material;

    public function delete(Material $material): void;
}
