<?php

namespace App\Modules\MaterialManager\Domain\Services;

interface FormServiceInterface
{
    /**
     * @param  array<int>  $ids
     * @return array<int, array<string, mixed>>
     */
    public function findActiveByIds(array $ids): array;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function findAllActive(): array;
}
