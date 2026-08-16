<?php

declare(strict_types=1);

namespace App\Modules\GalleryManager\Domain\ValueObjects;

enum GalleryStatus: int
{
    case DRAFT = 0;
    case PUBLISHED = 1;

    public function isPublished(): bool
    {
        return $this === self::PUBLISHED;
    }

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Черновик',
            self::PUBLISHED => 'Опубликовано',
        };
    }
}
