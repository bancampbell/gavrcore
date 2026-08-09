<?php

namespace App\Modules\MaterialManager\Domain\ValueObjects;

enum MaterialStatus: string
{
    case PUBLISHED = 'published';
    case DRAFT = 'draft';
    case ARCHIVED = 'archived';
    case TRASH = 'trash';

    public function label(): string
    {
        return match($this) {
            self::PUBLISHED => 'Опубликовано',
            self::DRAFT => 'Не опубликовано',
            self::ARCHIVED => 'Архив',
            self::TRASH => 'Корзина',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::PUBLISHED => 'emerald',
            self::DRAFT => 'rose',
            self::ARCHIVED => 'slate',
            self::TRASH => 'gray',
        };
    }

    public function isPublished(): bool
    {
        return $this === self::PUBLISHED;
    }

    public function isDraft(): bool
    {
        return $this === self::DRAFT;
    }

    public function isTrash(): bool
    {
        return $this === self::TRASH;
    }

    public function canPublish(): bool
    {
        return $this === self::DRAFT || $this === self::ARCHIVED;
    }

    public function canUnpublish(): bool
    {
        return $this === self::PUBLISHED;
    }

    public function canDelete(): bool
    {
        return $this !== self::TRASH;
    }

    public function canRestore(): bool
    {
        return $this === self::TRASH;
    }

    public function canForceDelete(): bool
    {
        return $this === self::TRASH;
    }
}
