<?php

namespace App\Modules\MaterialManager\Application\UseCases;

use App\Modules\MaterialManager\Domain\Entities\Material;
use App\Modules\MaterialManager\Domain\Events\MaterialPublished;
use App\Modules\MaterialManager\Domain\Repositories\MaterialRepositoryInterface;
use Illuminate\Support\Facades\Event;

final readonly class BulkPublishUseCase
{
    public function __construct(
        private MaterialRepositoryInterface $repository,
    ) {}

    public function execute(array $ids, int $actorId): int
    {
        $materials = array_filter(
            $this->repository->findMany($ids),
            fn (Material $material) => $material->status->canPublish(),
        );

        $published = $this->repository->bulkPublish($ids);

        foreach ($materials as $material) {
            Event::dispatch(new MaterialPublished($material, $actorId));
        }

        return $published;
    }
}
