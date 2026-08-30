<?php

namespace App\Modules\MenuManager\Application\UseCases;

use App\Modules\MenuManager\Application\DTO\UpdateMenuItemData;
use App\Modules\MenuManager\Domain\Repositories\MenuItemRepositoryInterface;
use App\Modules\MenuManager\Domain\Services\SlugGeneratorInterface;
use App\Modules\MenuManager\Infrastructure\Models\MenuItemModel;

class UpdateMenuItemUseCase
{
    public function __construct(
        private MenuItemRepositoryInterface $repository,
        private SlugGeneratorInterface $slugGenerator,
    ) {}

    public function execute(int $id, UpdateMenuItemData $data): MenuItemModel
    {
        $item = $this->repository->findById($id);
        if (! $item) throw new \RuntimeException('Menu item not found');

        $updateData = [
            'parent_id' => $data->parentId ?? $item->parent_id,
            'title' => $data->title ?? $item->title,
            'alias' => $data->alias ?? ($data->title ? $this->slugGenerator->generate($data->title) : $item->alias),
            'link_type' => $data->linkType ?? $item->link_type,
            'link_value' => $data->linkValue ?? $item->link_value,
            'target' => $data->target ?? $item->target,
            'status' => $data->status ?? $item->status,
            'access' => $data->access ?? $item->access,
            'language' => $data->language ?? $item->language,
        ];

        $oldParentId = $item->parent_id;
        $newParentId = $updateData['parent_id'];
        $oldOrdering = $item->ordering;

        if ($data->position === 'first' || $data->afterId) {
            $this->repository->decrementOrdering($item->menu_type_id, $oldParentId, $oldOrdering);
            if ($data->position === 'first') {
                $this->repository->incrementOrdering($item->menu_type_id, $newParentId, 0);
                $updateData['ordering'] = 0;
            } elseif ($data->afterId) {
                $afterItem = $this->repository->findById($data->afterId);
                if ($afterItem && $afterItem->menu_type_id === $item->menu_type_id) {
                    $newOrdering = $afterItem->ordering + 1;
                    $this->repository->incrementOrdering($item->menu_type_id, $newParentId, $newOrdering);
                    $updateData['ordering'] = $newOrdering;
                } else {
                    $maxOrdering = $this->repository->getMaxOrdering($item->menu_type_id, $newParentId);
                    $updateData['ordering'] = $maxOrdering + 1;
                }
            }
        } elseif ($oldParentId != $newParentId) {
            $this->repository->decrementOrdering($item->menu_type_id, $oldParentId, $oldOrdering);
            $maxOrdering = $this->repository->getMaxOrdering($item->menu_type_id, $newParentId);
            $updateData['ordering'] = $maxOrdering + 1;
        }

        return $this->repository->update($id, $updateData);
    }
}