<?php

namespace App\Modules\MenuManager\Application\UseCases;

use App\Modules\MenuManager\Application\DTO\CreateMenuTypeData;
use App\Modules\MenuManager\Domain\Repositories\MenuTypeRepositoryInterface;
use App\Modules\MenuManager\Domain\Services\SlugGeneratorInterface;
use App\Modules\MenuManager\Infrastructure\Models\MenuTypeModel;

class CreateMenuTypeUseCase
{
    public function __construct(
        private MenuTypeRepositoryInterface $repository,
        private SlugGeneratorInterface $slugGenerator,
    ) {}

    public function execute(CreateMenuTypeData $data): MenuTypeModel
    {
        $alias = $data->alias ?? $this->slugGenerator->generate($data->title);
        return $this->repository->create([
            'title' => $data->title,
            'alias' => $alias,
            'description' => $data->description,
            'ordering' => $data->ordering,
            'status' => $data->status,
        ]);
    }
}