<?php

namespace App\Modules\MaterialManager\Application\UseCases;

use App\Modules\MaterialManager\Domain\Events\MaterialForceDeleted;
use App\Modules\MaterialManager\Domain\Repositories\MaterialRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

final readonly class ForceDeleteMaterialUseCase
{
    public function __construct(
        private MaterialRepositoryInterface $repository,
    ) {}

    public function execute(int $id, int $actorId): void
    {
        DB::transaction(function () use ($id, $actorId) {
            $material = $this->repository->findById($id);

            if (!$material) {
                throw new \DomainException('Material not found');
            }

            if (!$material->status->canForceDelete()) {
                throw new \DomainException('Cannot force delete material with status: ' . $material->status->value);
            }

            $this->repository->forceDelete($material);

            DB::afterCommit(fn () => Event::dispatch(new MaterialForceDeleted($material, $actorId)));
        });
    }
}
