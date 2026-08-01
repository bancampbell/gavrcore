<?php

namespace Modules\MediaManager\Application\UseCases;

use Modules\MediaManager\Application\DTO\CopyItemData;
use Modules\MediaManager\Domain\Repositories\MediaRepositoryInterface;

class CopyItemUseCase
{
    public function __construct(
        private MediaRepositoryInterface $repository
    ) {}

    public function execute(CopyItemData $data): void
    {
        if (!$this->repository->exists($data->path)) {
            throw new \RuntimeException('Файл или папка не найдены');
        }

        $this->repository->copy($data->path);
    }
}
