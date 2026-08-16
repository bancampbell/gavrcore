<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\GalleryManager\Domain\ValueObjects;

use App\Modules\GalleryManager\Domain\ValueObjects\GalleryStatus;
use PHPUnit\Framework\TestCase;

class GalleryStatusTest extends TestCase
{
    public function test_enum_cases_have_correct_values(): void
    {
        $this->assertEquals(0, GalleryStatus::DRAFT->value);
        $this->assertEquals(1, GalleryStatus::PUBLISHED->value);
    }

    public function test_is_published_returns_correct_boolean(): void
    {
        $this->assertFalse(GalleryStatus::DRAFT->isPublished());
        $this->assertTrue(GalleryStatus::PUBLISHED->isPublished());
    }

    public function test_label_returns_russian_translation(): void
    {
        $this->assertEquals('Черновик', GalleryStatus::DRAFT->label());
        $this->assertEquals('Опубликовано', GalleryStatus::PUBLISHED->label());
    }

    public function test_from_creates_enum_from_integer(): void
    {
        $this->assertEquals(GalleryStatus::DRAFT, GalleryStatus::from(0));
        $this->assertEquals(GalleryStatus::PUBLISHED, GalleryStatus::from(1));
    }
}
