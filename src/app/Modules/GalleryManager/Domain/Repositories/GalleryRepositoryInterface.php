<?php

declare(strict_types=1);

namespace App\Modules\GalleryManager\Domain\Repositories;

use App\Modules\GalleryManager\Domain\Entities\Gallery;
use App\Modules\GalleryManager\Domain\Entities\GalleryImage;
use Illuminate\Support\Collection;

interface GalleryRepositoryInterface
{
    public function findById(int $id): ?Gallery;
    public function findByIdWithImages(int $id): ?Gallery;
    public function findImageById(int $imageId): ?GalleryImage;
    public function getAll(): Collection;
    public function getAllWithImageCount(): Collection;
    public function create(Gallery $gallery): Gallery;
    public function update(Gallery $gallery): Gallery;
    public function delete(int $id): void;
    public function addImage(int $galleryId, array $data): GalleryImage;
    public function updateImage(int $imageId, array $data): GalleryImage;
    public function deleteImage(int $imageId): void;
}
