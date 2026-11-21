<?php

namespace App\Modules\MaterialManager\Application\UseCases;

use App\Modules\MaterialManager\Application\Services\HomepageManager;
use App\Modules\MaterialManager\Domain\Entities\Material;
use App\Modules\MaterialManager\Domain\Events\MaterialDeleted;
use App\Modules\MaterialManager\Domain\Repositories\MaterialRepositoryInterface;
use Illuminate\Support\Facades\Event;

final readonly class BulkDeleteUseCase
{
    public function __construct(
        private MaterialRepositoryInterface $repository,
        private HomepageManager $homepageManager,
    ) {}

    public function execute(array $ids, int $actorId): int
    {
        // Снимки сущностей до операции — репозиторий меняет состояние
        // массовым update'ом, а события несут доменную сущность.
        $materials = array_filter(
            $this->repository->findMany($ids),
            fn (Material $material) => $material->status->canDelete(),
        );

        $homepageId = $this->homepageManager->currentMaterialId();

        $deleted = $this->repository->bulkDelete($ids);

        if ($homepageId !== null && in_array($homepageId, array_map('intval', $ids), true)) {
            $this->homepageManager->unassign();
        }

        foreach ($materials as $material) {
            Event::dispatch(new MaterialDeleted($material, $actorId));
        }

        return $deleted;
    }
}
