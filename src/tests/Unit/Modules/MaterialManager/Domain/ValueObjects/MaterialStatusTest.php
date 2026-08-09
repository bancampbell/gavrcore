<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\MaterialManager\Domain\ValueObjects;

use App\Modules\MaterialManager\Domain\ValueObjects\MaterialStatus;
use PHPUnit\Framework\TestCase;

class MaterialStatusTest extends TestCase
{
    public function test_published_status(): void
    {
        $status = MaterialStatus::PUBLISHED;

        $this->assertTrue($status->isPublished());
        $this->assertFalse($status->canPublish());
        $this->assertTrue($status->canUnpublish());
        $this->assertTrue($status->canDelete());
        $this->assertFalse($status->canRestore());
    }

    public function test_draft_status(): void
    {
        $status = MaterialStatus::DRAFT;

        $this->assertTrue($status->isDraft());
        $this->assertTrue($status->canPublish());
        $this->assertFalse($status->canUnpublish());
        $this->assertTrue($status->canDelete());
        $this->assertFalse($status->canRestore());
    }

    public function test_archived_status(): void
    {
        $status = MaterialStatus::ARCHIVED;

        $this->assertTrue($status->canPublish());
        $this->assertFalse($status->canUnpublish());
        $this->assertTrue($status->canDelete());
    }

    public function test_trash_status(): void
    {
        $status = MaterialStatus::TRASH;

        $this->assertTrue($status->isTrash());
        $this->assertFalse($status->canPublish());
        $this->assertFalse($status->canDelete());
        $this->assertTrue($status->canRestore());
        $this->assertTrue($status->canForceDelete());
    }

    public function test_label_returns_russian_text(): void
    {
        $this->assertSame('Опубликовано', MaterialStatus::PUBLISHED->label());
        $this->assertSame('Не опубликовано', MaterialStatus::DRAFT->label());
        $this->assertSame('Архив', MaterialStatus::ARCHIVED->label());
        $this->assertSame('Корзина', MaterialStatus::TRASH->label());
    }

    public function test_color_returns_expected_values(): void
    {
        $this->assertSame('emerald', MaterialStatus::PUBLISHED->color());
        $this->assertSame('rose', MaterialStatus::DRAFT->color());
        $this->assertSame('slate', MaterialStatus::ARCHIVED->color());
        $this->assertSame('gray', MaterialStatus::TRASH->color());
    }
}
