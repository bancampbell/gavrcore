<?php

namespace App\Modules\MenuManager\Application\UseCases;

use App\Modules\MenuManager\Application\DTO\CreateMenuItemData;
use App\Modules\MenuManager\Domain\Repositories\MenuItemRepositoryInterface;
use App\Modules\MenuManager\Domain\Services\SlugGeneratorInterface;
use App\Modules\MenuManager\Infrastructure\Models\MenuItemModel;

class CreateMenuItemUseCase
{
    public function __construct(
        private MenuItemRepositoryInterface $repository,
        private SlugGeneratorInterface $slugGenerator,
    ) {}

    public function execute(CreateMenuItemData $data): MenuItemModel
    {
        $alias = $data->alias ?? $this->slugGenerator->generate($data->title);
        $insertData = [
            'menu_type_id' => $data->menuTypeId,
            'parent_id' => $data->parentId,
            'title' => $data->title,
            'alias' => $alias,
            'link_type' => $data->linkType,
            'link_value' => $data->linkValue,
            'target' => $data->target,
            'status' => $data->status,
            'access' => $data->access,
            'language' => $data->language,
        ];
        $maxOrdering = $this->repository->getMaxOrdering($data->menuTypeId, $data->parentId);
        if ($data->position === 'first') {
            $this->repository->incrementOrdering($data->menuTypeId, $data->parentId, 0);
            $insertData['ordering'] = 0;
        } elseif ($data->afterId) {
            $afterItem = $this->repository->findById($data->afterId);
            if ($afterItem && $afterItem->menu_type_id === $data->menuTypeId) {
                $newOrdering = $afterItem->ordering + 1;
                $this->repository->incrementOrdering($data->menuTypeId, $data->parentId, $newOrdering);
                $insertData['ordering'] = $newOrdering;
            } else {
                $insertData['ordering'] = $maxOrdering + 1;
            }
        } else {
            $insertData['ordering'] = $maxOrdering + 1;
        }
        return $this->repository->create($insertData);
    }
}