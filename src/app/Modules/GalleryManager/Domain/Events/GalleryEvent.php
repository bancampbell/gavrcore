<?php

declare(strict_types=1);

namespace App\Modules\GalleryManager\Domain\Events;

abstract class GalleryEvent
{
    public function __construct(public readonly int $galleryId) {}

    public function getGalleryId(): int
    {
        return $this->galleryId;
    }
}
