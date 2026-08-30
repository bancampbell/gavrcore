<?php

namespace App\Modules\MenuManager\Application\UseCases;

use App\Modules\MenuManager\Application\DTO\UpdateMenuTypeData;
use App\Modules\MenuManager\Domain\Repositories\MenuTypeRepositoryInterface;
use App\Modules\MenuManager\Infrastructure\Models\MenuTypeModel;

class UpdateMenuTypeUseCase
{
    public function __construct(private MenuTypeRepositoryInterface $repository) {}

    public function execute(int $id, UpdateMenuTypeData $data): MenuTypeModel
    {
        $existing = $this->repository->findById($id);
        if (! $existing) throw new \RuntimeException('Menu type not found');

        $updateData = array_filter([
            'title' => $data->title,
            'alias' => $data->alias ?? $existing->alias,
            'description' => $data->description,
            'ordering' => $data->ordering,
            'status' => $data->status,
        ], fn ($v) => $v !== null);

        return $this->repository->update($id, $updateData);
    }
}