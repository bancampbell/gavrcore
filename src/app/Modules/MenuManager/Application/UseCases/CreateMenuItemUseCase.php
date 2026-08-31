<?php

namespace App\Modules\MenuManager\Application\UseCases;

use App\Modules\MenuManager\Application\DTO\CreateMenuItemData;
use App\Modules\MenuManager\Domain\Repositories\MenuItemRepositoryInterface;
use App\Modules\MenuManager\Domain\Services\SlugGeneratorInterface;
use App\Modules\MenuManager\Infrastructure\Models\MenuItemModel;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class CreateMenuItemUseCase
{
    public function __construct(
        private MenuItemRepositoryInterface $repository,
        private SlugGeneratorInterface $slugGenerator,
    ) {}

    public function execute(CreateMenuItemData $data): MenuItemModel
    {
        $attempts = 0;
        $maxAttempts = 3;

        while ($attempts < $maxAttempts) {
            try {
                return DB::transaction(function () use ($data) {
                    $this->repository->lockByMenuType($data->menuTypeId);

                    $alias = $data->alias ?? $this->slugGenerator->generate($data->title);
                    $alias = $this->ensureUniqueAlias($alias, $data->menuTypeId);

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
            } catch (UniqueConstraintViolationException $e) {
                $attempts++;
                if ($attempts >= $maxAttempts) {
                    throw $e;
                }
                usleep(50000);
            }
        }

        throw new \RuntimeException('Failed to create menu item after multiple attempts');
    }

    private function ensureUniqueAlias(string $alias, int $menuTypeId): string
    {
        $originalAlias = $alias;
        $counter = 1;

        while ($this->repository->findByAliasWithLock($alias, $menuTypeId)) {
            $alias = $originalAlias . '-' . $counter++;
        }

        return $alias;
    }
}
