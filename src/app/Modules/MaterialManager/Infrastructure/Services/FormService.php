<?php

namespace App\Modules\MaterialManager\Infrastructure\Services;

use App\Models\Form;
use App\Modules\MaterialManager\Domain\Services\FormServiceInterface;

class FormService implements FormServiceInterface
{
    public function findActiveByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        return Form::whereIn('id', $ids)
            ->where('status', true)
            ->get()
            ->keyBy('id')
            ->toArray();
    }

    public function findAllActive(): array
    {
        return Form::where('status', true)
            ->get()
            ->keyBy('id')
            ->toArray();
    }
}
