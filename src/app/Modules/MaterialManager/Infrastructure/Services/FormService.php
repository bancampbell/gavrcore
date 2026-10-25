<?php

namespace App\Modules\MaterialManager\Infrastructure\Services;

use App\Modules\FormBuilder\Infrastructure\Models\FormModel;
use App\Modules\MaterialManager\Domain\Services\FormServiceInterface;

class FormService implements FormServiceInterface
{
    /**
     * @param  array<int>  $ids
     * @return array<int, array<string, mixed>>
     */
    public function findActiveByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        return FormModel::whereIn('id', $ids)
            ->where('status', true)
            ->get()
            ->keyBy('id')
            ->toArray();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function findAllActive(): array
    {
        return FormModel::where('status', true)
            ->get()
            ->keyBy('id')
            ->toArray();
    }
}
