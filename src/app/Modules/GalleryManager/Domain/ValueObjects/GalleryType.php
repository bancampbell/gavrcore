<?php

declare(strict_types=1);

namespace App\Modules\GalleryManager\Domain\ValueObjects;

enum GalleryType: string
{
    case GRID = 'grid';
    case SLIDESHOW = 'slideshow';
    case SLIDER = 'slider';
    case SWITCHER = 'switcher';

    public function label(): string
    {
        return match ($this) {
            self::GRID => 'Сетка',
            self::SLIDESHOW => 'Слайд-шоу',
            self::SLIDER => 'Слайдер',
            self::SWITCHER => 'Switcher',
        };
    }
}
