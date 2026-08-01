<?php

namespace Modules\MediaManager\Application\UseCases;

use Modules\MediaManager\Application\DTO\DeleteItemsData;
use Modules\MediaManager\Application\DTO\OperationResult;
use Modules\MediaManager\Domain\Repositories\MediaRepositoryInterface;

class DeleteItemsUseCase
{
    public function __construct(
        private MediaRepositoryInterface $repository
    ) {}

    public function execute(DeleteItemsData $data): OperationResult
    {
        $deleted = [];
        $failed = [];

        foreach ($data->paths as $path) {
            if (!$this->repository->exists($path)) {
                $failed[] = ['path' => $path, 'reason' => 'not_found'];
                continue;
            }

            try {
                $this->repository->delete($path);
                $deleted[] = $path;
            } catch (\RuntimeException $e) {
                $failed[] = ['path' => $path, 'reason' => $e->getMessage()];
            }
        }

        return new OperationResult(
            success: empty($failed),
            message: empty($failed)
                ? 'Удалено элементов: ' . count($deleted)
                : 'Удалено: ' . count($deleted) . ', ошибок: ' . count($failed),
            data: ['deleted' => $deleted],
            errors: $failed,
        );
    }
}
