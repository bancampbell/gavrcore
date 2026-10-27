<?php

namespace App\Modules\MaterialManager\Application\UseCases;

use App\Modules\MaterialManager\Application\Services\HomepageManager;
use App\Modules\MaterialManager\Domain\Repositories\MaterialRepositoryInterface;

final readonly class BulkDeleteUseCase
{
    public function __construct(
        private MaterialRepositoryInterface $repository,
        private HomepageManager $homepageManager,
    ) {}

    public function execute(array $ids, int $actorId): int
    {
        $homepageId = $this->homepageManager->currentMaterialId();

        $deleted = $this->repository->bulkDelete($ids);

        if ($homepageId !== null && in_array($homepageId, array_map('intval', $ids), true)) {
            $this->homepageManager->unassign();
        }

        return $deleted;
    }
}
