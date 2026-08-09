<?php

namespace App\Modules\MaterialManager\Application\UseCases;

use App\Modules\MaterialManager\Domain\Events\MaterialRestored;
use App\Modules\MaterialManager\Domain\Repositories\MaterialRepositoryInterface;
use App\Modules\MaterialManager\Domain\Services\ClockInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

final readonly class RestoreMaterialUseCase
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

            $restored = $material->restore($this->clock->now());
            $this->repository->restore($restored);

            DB::afterCommit(fn () => Event::dispatch(new MaterialRestored($restored, $actorId)));
        });
    }
}
