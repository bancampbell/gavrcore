<?php

namespace App\Modules\MenuManager\Application\UseCases;

use App\Modules\MenuManager\Application\DTO\CreateMenuItemData;
use App\Modules\MenuManager\Domain\Repositories\MenuItemRepositoryInterface;
use App\Modules\MenuManager\Infrastructure\Models\MenuItemModel;
use Illuminate\Support\Facades\DB;

class CreateMenuItemUseCase
{
    public function __construct(
        private MenuItemRepositoryInterface $repository,
    ) {}

    public function execute(CreateMenuItemData $data): MenuItemModel
    {
        return DB::transaction(function () use ($data) {
            $this->repository->lockByMenuType($data->menuTypeId);

            $insertData = [
                'menu_type_id' => $data->menuTypeId,
                'parent_id' => $data->parentId,
                'title' => $data->title,
                'link_type' => $data->linkType,
                'link_value' => $data->linkValue,
                'target' => $data->target,
                'status' => $data->status,
                'access' => $data->access,
                'language' => $data->language,
            ];

            $this->repository->lockByParent($data->menuTypeId, $data->parentId);
            $maxOrdering = $this->repository->getMaxOrdering($data->menuTypeId, $data->parentId);

            if ($data->position === 'first') {
                $this->repository->incrementOrdering($data->menuTypeId, $data->parentId, 0);
                $insertData['ordering'] = 0;
            } elseif ($data->afterId) {
                $afterItem = $this->repository->findById($data->afterId);
                // FIX: нормализация типов перед строгим сравнением
                if ($afterItem && (int) $afterItem->menu_type_id === (int) $data->menuTypeId) {
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
        });
    }
}
