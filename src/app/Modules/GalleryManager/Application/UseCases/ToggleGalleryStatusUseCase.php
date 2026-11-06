<?php

declare(strict_types=1);

namespace App\Modules\GalleryManager\Application\UseCases;

use App\Modules\GalleryManager\Domain\Repositories\GalleryRepositoryInterface;
use Illuminate\Support\Facades\DB;

class ToggleGalleryStatusUseCase
{
    public function __construct(
        private readonly GalleryRepositoryInterface $repository,
    ) {}

    public function execute(int $id, bool $publish): void
    {
        // Чтение с блокировкой и запись в одной транзакции —
        // исключает lost update при конкурентном update/toggle.
        DB::transaction(function () use ($id, $publish) {
            $gallery = $this->repository->findByIdForUpdate($id);

            if (! $gallery) {
                throw new \RuntimeException('Gallery not found');
            }

            if ($publish) {
                $gallery->publish();
            } else {
                $gallery->unpublish();
            }

            $this->repository->update($gallery);
        });
    }
}
