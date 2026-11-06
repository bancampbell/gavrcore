<?php

declare(strict_types=1);

namespace App\Modules\GalleryManager\Application\UseCases;

use App\Modules\GalleryManager\Application\DTO\UpdateGalleryData;
use App\Modules\GalleryManager\Domain\Repositories\GalleryRepositoryInterface;
use App\Modules\GalleryManager\Domain\ValueObjects\GalleryStatus;
use Illuminate\Support\Facades\DB;

class UpdateGalleryUseCase
{
    public function __construct(
        private readonly GalleryRepositoryInterface $repository,
    ) {}

    public function execute(int $id, UpdateGalleryData $data): void
    {
        // Чтение с блокировкой и запись в одной транзакции —
        // исключает lost update при конкурентном toggle.
        DB::transaction(function () use ($id, $data) {
            $gallery = $this->repository->findByIdForUpdate($id);

            if (! $gallery) {
                throw new \RuntimeException('Gallery not found');
            }

            $gallery->update(
                title: $data->title,
                type: $data->type,
                settings: $data->settings,
                status: $data->status ? GalleryStatus::PUBLISHED : GalleryStatus::DRAFT,
            );

            $this->repository->update($gallery);
        });
    }
}
