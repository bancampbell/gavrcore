<?php

namespace Modules\MediaManager\Application\UseCases;

use Illuminate\Support\Facades\Event;
use Modules\MediaManager\Application\DTO\DeleteItemData;
use Modules\MediaManager\Domain\Events\MediaDeleted;
use Modules\MediaManager\Domain\Repositories\MediaRepositoryInterface;

class DeleteItemUseCase
{
    public function __construct(
        private MediaRepositoryInterface $repository
    ) {}

    public function execute(DeleteItemData $data): void
    {
        if (! $this->repository->exists($data->path)) {
            throw new \RuntimeException('Файл или папка не найдены');
        }

        $this->repository->delete($data->path);

        Event::dispatch(new MediaDeleted(auth()->id(), $data->path));
    }
}
