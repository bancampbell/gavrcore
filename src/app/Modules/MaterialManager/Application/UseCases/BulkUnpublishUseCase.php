<?php

namespace App\Modules\MaterialManager\Application\UseCases;

use App\Modules\MaterialManager\Domain\Repositories\MaterialRepositoryInterface;

final readonly class BulkUnpublishUseCase
{
    public function __construct(
        private MaterialRepositoryInterface $repository,
    ) {}

    public function execute(array $ids, int $actorId): int
    {
        return $this->repository->bulkUnpublish($ids);
    }
}
