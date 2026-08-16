<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\GalleryManager\Domain\ValueObjects;

use App\Modules\GalleryManager\Domain\ValueObjects\GalleryType;
use PHPUnit\Framework\TestCase;

class GalleryTypeTest extends TestCase
{
    public function test_enum_cases_have_correct_values(): void
    {
        $this->assertEquals('grid', GalleryType::GRID->value);
        $this->assertEquals('slideshow', GalleryType::SLIDESHOW->value);
        $this->assertEquals('slider', GalleryType::SLIDER->value);
        $this->assertEquals('switcher', GalleryType::SWITCHER->value);
    }

    public function test_label_returns_russian_translation(): void
    {
        $this->assertEquals('Сетка', GalleryType::GRID->label());
        $this->assertEquals('Слайд-шоу', GalleryType::SLIDESHOW->label());
        $this->assertEquals('Слайдер', GalleryType::SLIDER->label());
        $this->assertEquals('Switcher', GalleryType::SWITCHER->label());
    }

    public function test_from_creates_enum_from_valid_string(): void
    {
        $this->assertEquals(GalleryType::GRID, GalleryType::from('grid'));
        $this->assertEquals(GalleryType::SLIDER, GalleryType::from('slider'));
    }

    public function test_from_throws_exception_for_invalid_value(): void
    {
        $this->expectException(\ValueError::class);
        GalleryType::from('invalid');
    }
}
