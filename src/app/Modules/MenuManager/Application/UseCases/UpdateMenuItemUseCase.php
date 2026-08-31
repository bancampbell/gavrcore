<?php

namespace App\Modules\MenuManager\Application\UseCases;

use App\Modules\MenuManager\Application\DTO\Optional;
use App\Modules\MenuManager\Application\DTO\UpdateMenuItemData;
use App\Modules\MenuManager\Domain\Repositories\MenuItemRepositoryInterface;
use App\Modules\MenuManager\Domain\Services\SlugGeneratorInterface;
use App\Modules\MenuManager\Infrastructure\Models\MenuItemModel;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class UpdateMenuItemUseCase
{
    public function __construct(
        private MenuItemRepositoryInterface $repository,
        private SlugGeneratorInterface $slugGenerator,
    ) {}

    public function execute(int $id, UpdateMenuItemData $data): MenuItemModel
    {
        $attempts = 0;
        $maxAttempts = 3;

        while ($attempts < $maxAttempts) {
            try {
                return DB::transaction(function () use ($id, $data) {
                    $preliminary = $this->repository->findById($id);
                    if (! $preliminary) {
                        throw new \RuntimeException('Menu item not found');
                    }

                    $newMenuTypeId = $data->menuTypeId ?? $preliminary->menu_type_id;
                    $oldMenuTypeId = (int) $preliminary->menu_type_id;
                    $newMenuTypeId = (int) $newMenuTypeId;
                    $isMenuTypeChanged = $oldMenuTypeId !== $newMenuTypeId;

                    // Глобальный порядок блокировок: сначала coarse (menu type), потом fine (row / parent).
                    // При смене меню блокируем оба типа в порядке возрастания id,
                    // чтобы исключить deadlock при кросс-переносе пунктов.
                    if ($isMenuTypeChanged) {
                        $firstLock = min($oldMenuTypeId, $newMenuTypeId);
                        $secondLock = max($oldMenuTypeId, $newMenuTypeId);
                        $this->repository->lockByMenuType($firstLock);
                        if ($secondLock !== $firstLock) {
                            $this->repository->lockByMenuType($secondLock);
                        }
                    } else {
                        $this->repository->lockByMenuType($oldMenuTypeId);
                    }

                    $item = $this->repository->findByIdWithLock($id);
                    if (! $item) {
                        throw new \RuntimeException('Menu item not found');
                    }

                    $alias = $item->alias;
                    if ($data->alias !== null) {
                        $alias = $data->alias;
                    } elseif ($data->title !== null && $data->alias === null) {
                        $alias = $this->slugGenerator->generate($data->title);
                    }

                    if ($alias !== $item->alias || $isMenuTypeChanged) {
                        $alias = $this->ensureUniqueAlias($alias, $newMenuTypeId, $item->id);
                    }

                    $updateData = [
                        'menu_type_id' => $newMenuTypeId,
                        'title' => $data->title ?? $item->title,
                        'alias' => $alias,
                        'link_type' => $data->linkType ?? $item->link_type,
                        'link_value' => $data->linkValue ?? $item->link_value,
                        'target' => $data->target ?? $item->target,
                        'status' => $data->status ?? $item->status,
                        'access' => $data->access ?? $item->access,
                        'language' => $data->language ?? $item->language,
                    ];

                    if (! $data->parentId instanceof Optional) {
                        $updateData['parent_id'] = $data->parentId;
                    } elseif ($isMenuTypeChanged) {
                        $updateData['parent_id'] = null;
                    }

                    $oldParentId = $item->parent_id;
                    $newParentId = array_key_exists('parent_id', $updateData)
                        ? $updateData['parent_id']
                        : $item->parent_id;

                    if ($newParentId !== null) {
                        $parentItem = $this->repository->findById($newParentId);
                        if (! $parentItem || $parentItem->menu_type_id != $newMenuTypeId) {
                            throw new \RuntimeException('Parent item does not belong to the menu type');
                        }
                    }

                    if ($newParentId !== null && ($newParentId == $item->id || $this->repository->isDescendant($item->id, $newParentId))) {
                        throw new \RuntimeException('Cannot set a descendant as parent');
                    }

                    if ($data->position === 'first' || $data->afterId) {
                        $this->repository->lockByParent($item->menu_type_id, $oldParentId);
                        if ($newParentId !== $oldParentId || $isMenuTypeChanged) {
                            $this->repository->lockByParent($newMenuTypeId, $newParentId);
                        }

                        if ($oldParentId !== $newParentId || $isMenuTypeChanged) {
                            $this->repository->decrementOrdering($item->menu_type_id, $oldParentId, $item->ordering);
                        }

                        if ($data->position === 'first') {
                            $this->repository->incrementOrdering($newMenuTypeId, $newParentId, 0);
                            $updateData['ordering'] = 0;
                        } elseif ($data->afterId) {
                            $afterItem = $this->repository->findById($data->afterId);
                            if ($afterItem && (int) $afterItem->menu_type_id === $newMenuTypeId) {
                                $newOrdering = $afterItem->ordering + 1;
                                $this->repository->incrementOrdering($newMenuTypeId, $newParentId, $newOrdering);
                                $updateData['ordering'] = $newOrdering;
                            } else {
                                $maxOrdering = $this->repository->getMaxOrdering($newMenuTypeId, $newParentId);
                                $updateData['ordering'] = $maxOrdering + 1;
                            }
                        }
                    } elseif ($oldParentId != $newParentId || $isMenuTypeChanged) {
                        $this->repository->lockByParent($item->menu_type_id, $oldParentId);
                        $this->repository->lockByParent($newMenuTypeId, $newParentId);

                        $this->repository->decrementOrdering($item->menu_type_id, $oldParentId, $item->ordering);
                        $maxOrdering = $this->repository->getMaxOrdering($newMenuTypeId, $newParentId);
                        $updateData['ordering'] = $maxOrdering + 1;
                    }

                    return $this->repository->update($id, $updateData);
                });
            } catch (UniqueConstraintViolationException $e) {
                $attempts++;
                if ($attempts >= $maxAttempts) {
                    throw $e;
                }
                usleep(50000);
            }
        }

        throw new \RuntimeException('Failed to update menu item after multiple attempts');
    }

    private function ensureUniqueAlias(string $alias, int $menuTypeId, ?int $excludeId = null): string
    {
        $originalAlias = $alias;
        $counter = 1;

        while ($found = $this->repository->findByAliasWithLock($alias, $menuTypeId)) {
            if ($excludeId && $found->id === $excludeId) {
                break;
            }
            $alias = $originalAlias . '-' . $counter++;
        }

        return $alias;
    }
}
