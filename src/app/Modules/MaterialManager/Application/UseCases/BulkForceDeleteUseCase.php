<?php

namespace App\Modules\MaterialManager\Application\UseCases;

use App\Modules\MaterialManager\Domain\Entities\Material;
use App\Modules\MaterialManager\Domain\Events\MaterialForceDeleted;
use App\Modules\MaterialManager\Domain\Repositories\MaterialRepositoryInterface;
use Illuminate\Support\Facades\Event;

final readonly class BulkForceDeleteUseCase
{
    public function __construct(
        private MaterialRepositoryInterface $repository,
    ) {}

    public function execute(array $ids, int $actorId): int
    {
        // Снимки до удаления: после bulkForceDelete строк в таблице нет.
        $materials = array_filter(
            $this->repository->findMany($ids),
            fn (Material $material) => $material->status->canForceDelete(),
        );

        $deleted = $this->repository->bulkForceDelete($ids);

        foreach ($materials as $material) {
            Event::dispatch(new MaterialForceDeleted($material, $actorId));
        }

        return $deleted;
    }
}
