<?php

declare(strict_types=1);

namespace App\Modules\GalleryManager\Application\UseCases;

use App\Modules\GalleryManager\Domain\Repositories\GalleryRepositoryInterface;

class ToggleGalleryStatusUseCase
{
    public function __construct(
        private readonly GalleryRepositoryInterface $repository,
    ) {}

    public function execute(int $id, bool $publish): void
    {
        // Блокировка строки и запись в одной транзакции живут в GalleryRepository::update(),
        // что исключает lost update при конкурентном update/toggle.
        $gallery = $this->repository->findById($id);

        if (! $gallery) {
            throw new \RuntimeException('Gallery not found');
        }

        if ($publish) {
            $gallery->publish();
        } else {
            $gallery->unpublish();
        }

        $this->repository->update($gallery);
    }
}
