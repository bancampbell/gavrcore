<?php

declare(strict_types=1);

namespace App\Modules\GalleryManager\Infrastructure\Repositories;

use App\Modules\GalleryManager\Domain\Entities\Gallery;
use App\Modules\GalleryManager\Domain\Entities\GalleryImage;
use App\Modules\GalleryManager\Domain\Events\GalleryCreated;
use App\Modules\GalleryManager\Domain\Repositories\GalleryRepositoryInterface;
use App\Modules\GalleryManager\Domain\ValueObjects\GalleryStatus;
use App\Modules\GalleryManager\Domain\ValueObjects\GalleryType;
use App\Modules\GalleryManager\Infrastructure\Models\GalleryImageModel;
use App\Modules\GalleryManager\Infrastructure\Models\GalleryModel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GalleryRepository implements GalleryRepositoryInterface
{
    public function findById(int $id): ?Gallery
    {
        $model = GalleryModel::find($id);
        return $model ? $this->toEntity($model) : null;
    }

    public function findByIdWithImages(int $id): ?Gallery
    {
        $model = GalleryModel::with('images')->find($id);
        return $model ? $this->toEntity($model) : null;
    }

    public function findImageById(int $imageId): ?GalleryImage
    {
        $model = GalleryImageModel::find($imageId);
        return $model ? $this->imageToEntity($model) : null;
    }

    public function getAll(): Collection
    {
        return GalleryModel::orderBy('created_at', 'desc')->get()->map(fn ($m) => $this->toEntity($m));
    }

    public function getAllWithImageCount(): Collection
    {
        return GalleryModel::withCount('images')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($m) => $this->toEntity($m));
    }

    public function create(Gallery $gallery): Gallery
    {
        $model = GalleryModel::create([
            'title' => $gallery->title,
            'type' => $gallery->type->value,
            'settings' => $gallery->settings,
            'status' => $gallery->status->value,
            'ordering' => $gallery->ordering,
        ]);

        event(new GalleryCreated($model->id));

        return new Gallery(
            id: $model->id,
            title: $model->title,
            type: GalleryType::from($model->type),
            settings: $model->settings ?? [],
            status: GalleryStatus::from((int) $model->status),
            ordering: $model->ordering,
            createdAt: $model->created_at?->toDateTimeString(),
            updatedAt: $model->updated_at?->toDateTimeString(),
        );
    }

    public function update(Gallery $gallery): Gallery
    {
        $model = GalleryModel::findOrFail($gallery->id);
        $model->update([
            'title' => $gallery->title,
            'type' => $gallery->type->value,
            'settings' => $gallery->settings,
            'status' => $gallery->status->value,
            'ordering' => $gallery->ordering,
        ]);

        foreach ($gallery->releaseEvents() as $event) {
            event($event);
        }

        return new Gallery(
            id: $model->id,
            title: $model->title,
            type: GalleryType::from($model->type),
            settings: $model->settings ?? [],
            status: GalleryStatus::from((int) $model->status),
            ordering: $model->ordering,
            createdAt: $model->created_at?->toDateTimeString(),
            updatedAt: $model->updated_at?->toDateTimeString(),
        );
    }

    public function delete(int $id): void
    {
        GalleryModel::destroy($id);
    }

    public function addImage(int $galleryId, array $data): GalleryImage
    {
        $model = DB::transaction(function () use ($galleryId, $data) {
            $lastImage = GalleryImageModel::where('gallery_id', $galleryId)
                ->lockForUpdate()
                ->orderBy('ordering', 'desc')
                ->first();

            $maxOrdering = $lastImage ? $lastImage->ordering : 0;

            return GalleryImageModel::create([
                'gallery_id' => $galleryId,
                'image_path' => $data['image_path'],
                'title' => $data['title'] ?? null,
                'description' => $data['description'] ?? null,
                'alt_text' => $data['alt_text'] ?? null,
                'link' => $data['link'] ?? null,
                'ordering' => $maxOrdering + 1,
                'status' => $data['status'] ?? true,
            ]);
        });

        return $this->imageToEntity($model);
    }

    public function updateImage(int $imageId, array $data): GalleryImage
    {
        $model = GalleryImageModel::findOrFail($imageId);

        $model->update([
            'title' => array_key_exists('title', $data) ? $data['title'] : $model->title,
            'description' => array_key_exists('description', $data) ? $data['description'] : $model->description,
            'alt_text' => array_key_exists('alt_text', $data) ? $data['alt_text'] : $model->alt_text,
            'link' => array_key_exists('link', $data) ? $data['link'] : $model->link,
        ]);

        return $this->imageToEntity($model);
    }

    public function deleteImage(int $imageId): void
    {
        GalleryImageModel::destroy($imageId);
    }

    private function toEntity(GalleryModel $model): Gallery
    {
        $images = [];
        if ($model->relationLoaded('images')) {
            $images = $model->images->map(fn ($img) => $this->imageToEntity($img))->toArray();
        }

        $gallery = new Gallery(
            id: $model->id,
            title: $model->title,
            type: GalleryType::from($model->type),
            settings: $model->settings ?? [],
            status: GalleryStatus::from((int) $model->status),
            ordering: $model->ordering,
            createdAt: $model->created_at?->toDateTimeString(),
            updatedAt: $model->updated_at?->toDateTimeString(),
            imagesCount: $model->images_count ?? null,
        );

        if (!empty($images)) {
            $gallery->setImages($images);
        }

        return $gallery;
    }

    private function imageToEntity(GalleryImageModel $model): GalleryImage
    {
        return new GalleryImage(
            id: $model->id,
            galleryId: $model->gallery_id,
            imagePath: $model->image_path,
            title: $model->title,
            description: $model->description,
            altText: $model->alt_text,
            link: $model->link,
            ordering: $model->ordering,
            status: (bool) $model->status,
            createdAt: $model->created_at?->toDateTimeString(),
            updatedAt: $model->updated_at?->toDateTimeString(),
        );
    }
}
