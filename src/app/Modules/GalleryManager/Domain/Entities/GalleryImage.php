<?php

declare(strict_types=1);

namespace App\Modules\GalleryManager\Domain\Entities;

class GalleryImage
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $galleryId,
        public readonly string $imagePath,
        public ?string $title,
        public ?string $description,
        public ?string $altText,
        public ?string $link,
        public int $ordering,
        public bool $status,
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null,
    ) {}

    public function updateMeta(
        ?string $title,
        ?string $description,
        ?string $altText,
        ?string $link,
    ): void {
        $this->title = $title;
        $this->description = $description;
        $this->altText = $altText;
        $this->link = $link;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'gallery_id' => $this->galleryId,
            'image_path' => $this->imagePath,
            'title' => $this->title,
            'description' => $this->description,
            'alt_text' => $this->altText,
            'link' => $this->link,
            'ordering' => $this->ordering,
            'status' => $this->status,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
