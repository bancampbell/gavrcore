<?php

namespace App\Modules\MaterialManager\Application\UseCases;

use App\Modules\MaterialManager\Domain\Events\MaterialHomepageToggled;
use App\Modules\MaterialManager\Domain\Repositories\MaterialRepositoryInterface;
use App\Modules\MaterialManager\Domain\Services\ClockInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;

final readonly class ToggleHomepageUseCase
{
    public function __construct(
        private MaterialRepositoryInterface $repository,
        private ClockInterface $clock,
    ) {}

    public function execute(int $id, bool $showOnHomepage, int $actorId): void
    {
        $lock = Cache::lock('material_manager_homepage', 10);
        if (!$lock->get()) {
            throw new \DomainException('Не удалось получить блокировку главной страницы');
        }
        try {
            DB::transaction(function () use ($id, $showOnHomepage, $actorId) {
                $material = $this->repository->findByIdWithLock($id);

                if (!$material) {
                    throw new \DomainException('Material not found');
                }

                $updated = $material->toggleHomepage($showOnHomepage, $this->clock->now());
                $this->repository->save($updated);

                if ($showOnHomepage) {
                    $this->repository->clearHomepageExcept($id);
                }

                DB::afterCommit(fn () => Event::dispatch(new MaterialHomepageToggled($updated, $actorId)));
            });
        } finally {
            $lock->release();
        }
    }
}
