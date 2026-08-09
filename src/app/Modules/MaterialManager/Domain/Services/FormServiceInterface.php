<?php

namespace App\Modules\MaterialManager\Domain\Services;

interface FormServiceInterface
{
    public function findActiveByIds(array $ids): array;
    public function findAllActive(): array;
}
