<?php

namespace App\Modules\MaterialManager\Domain\Services;

interface CategoryServiceInterface
{
    public function findById(int $id): ?object;
    public function findBySlug(string $slug): ?object;
    public function getAll(): array;
}
