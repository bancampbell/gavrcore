<?php

namespace App\Modules\MaterialManager\Application\UseCases;

use App\Modules\MaterialManager\Domain\Events\MaterialPublished;
use App\Modules\MaterialManager\Domain\Repositories\MaterialRepositoryInterface;
use App\Modules\MaterialManager\Domain\Services\ClockInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

final readonly class PublishMaterialUseCase
{
    public function __construct(
        private MaterialRepositoryInterface $repository,
        private ClockInterface $clock,
    ) {}

    public function execute(int $id, int $actorId): void
    {
        DB::transaction(function () use ($id, $actorId) {
            $material = $this->repository->findById($id);

            if (!$material) {
                throw new \DomainException('Material not found');
            }

            $published = $material->publish($this->clock->now());
            $this->repository->save($published);

            DB::afterCommit(fn () => Event::dispatch(new MaterialPublished($published, $actorId)));
        });
    }
}
