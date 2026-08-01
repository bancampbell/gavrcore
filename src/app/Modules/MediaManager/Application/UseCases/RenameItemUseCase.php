<?php

namespace Modules\MediaManager\Application\UseCases;

use Modules\MediaManager\Application\DTO\RenameItemData;
use Modules\MediaManager\Domain\Repositories\MediaRepositoryInterface;

class RenameItemUseCase
{
    public function __construct(
        private MediaRepositoryInterface $repository
    ) {}

    public function execute(RenameItemData $data): void
    {
        if (!$this->repository->exists($data->oldPath)) {
            throw new \RuntimeException('Файл или папка не найдены');
        }

        $this->repository->rename($data->oldPath, $data->newName);
    }
}
