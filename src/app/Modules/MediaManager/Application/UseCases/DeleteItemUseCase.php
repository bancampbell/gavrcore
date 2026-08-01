<?php

namespace Modules\MediaManager\Application\UseCases;

use Modules\MediaManager\Application\DTO\DeleteItemData;
use Modules\MediaManager\Domain\Repositories\MediaRepositoryInterface;

class DeleteItemUseCase
{
    public function __construct(
        private MediaRepositoryInterface $repository
    ) {}

    public function execute(DeleteItemData $data): void
    {
        if (!$this->repository->exists($data->path)) {
            throw new \RuntimeException('Файл или папка не найдены');
        }

        $this->repository->delete($data->path);
    }
}
