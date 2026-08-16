<?php

declare(strict_types=1);

namespace App\Modules\GalleryManager\Domain\Entities;

use App\Modules\GalleryManager\Domain\Events\GalleryUpdated;
use App\Modules\GalleryManager\Domain\ValueObjects\GalleryStatus;
use App\Modules\GalleryManager\Domain\ValueObjects\GalleryType;

class Gallery
{
    /** @var array<GalleryImage> */
    private array $images = [];
    private array $domainEvents = [];

    public function __construct(
        public readonly ?int $id,
        public string $title,
        public GalleryType $type,
        public array $settings,
        public GalleryStatus $status,
        public int $ordering,
        public readonly ?string $createdAt = null,
        public readonly ?string $updatedAt = null,
        public readonly ?int $imagesCount = null,
    ) {}

    public static function create(
        string $title,
        GalleryType $type,
        array $settings,
        GalleryStatus $status,
    ): self {
        return new self(
            id: null,
            title: $title,
            type: $type,
            settings: $settings,
            status: $status,
            ordering: 0,
        );
    }

    public function update(
        string $title,
        GalleryType $type,
        array $settings,
        GalleryStatus $status,
    ): void {
        $this->title = $title;
        $this->type = $type;
        $this->settings = $settings;
        $this->status = $status;

        $this->recordEvent(new GalleryUpdated($this->id ?? 0));
    }

    public function publish(): void
    {
        $this->status = GalleryStatus::PUBLISHED;
        $this->recordEvent(new GalleryUpdated($this->id ?? 0));
    }

    public function unpublish(): void
    {
        $this->status = GalleryStatus::DRAFT;
        $this->recordEvent(new GalleryUpdated($this->id ?? 0));
    }

    public function setImages(array $images): void
    {
        $this->images = $images;
    }

    public function addImage(GalleryImage $image): void
    {
        $this->images[] = $image;
    }

    /** @return array<GalleryImage> */
    public function images(): array
    {
        return $this->images;
    }

    public function isPublished(): bool
    {
        return $this->status === GalleryStatus::PUBLISHED;
    }

    /** @return array<object> */
    public function releaseEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];
        return $events;
    }

    private function recordEvent(object $event): void
    {
        $this->domainEvents[] = $event;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type->value,
            'settings' => $this->settings,
            'status' => $this->status->value,
            'ordering' => $this->ordering,
            'images' => array_map(fn ($img) => $img->toArray(), $this->images),
            'images_count' => $this->imagesCount,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
