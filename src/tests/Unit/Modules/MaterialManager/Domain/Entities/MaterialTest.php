<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\MaterialManager\Domain\Entities;

use App\Modules\MaterialManager\Domain\Entities\Material;
use App\Modules\MaterialManager\Domain\ValueObjects\MaterialAccess;
use App\Modules\MaterialManager\Domain\ValueObjects\MaterialStatus;
use App\Modules\MaterialManager\Domain\ValueObjects\MaterialUpdateData;
use App\Modules\MaterialManager\Domain\ValueObjects\NotSet;
use PHPUnit\Framework\TestCase;

class MaterialTest extends TestCase
{
    public function test_create_sets_default_status_to_draft(): void
    {
        $material = Material::create(
            title: 'Test',
            userId: 1,
            slug: 'test',
        );

        $this->assertSame(MaterialStatus::DRAFT, $material->status);
        $this->assertNull($material->publishedAt);
        $this->assertSame(0, $material->views);
    }

    public function test_create_sets_published_at_when_status_is_published(): void
    {
        $material = Material::create(
            title: 'Published',
            userId: 1,
            slug: 'published',
            status: MaterialStatus::PUBLISHED,
        );

        $this->assertSame(MaterialStatus::PUBLISHED, $material->status);
        $this->assertNotNull($material->publishedAt);
    }

    public function test_create_throws_on_empty_slug(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Material::create(title: 'Test', userId: 1, slug: '');
    }

    public function test_update_preserves_unchanged_fields_with_not_set(): void
    {
        $material = Material::create(title: 'Original', userId: 1, slug: 'original');

        $data = new MaterialUpdateData(
            title: 'Updated',
            slug: NotSet::instance(),
            content: NotSet::instance(),
            categoryId: NotSet::instance(),
            status: NotSet::instance(),
            access: NotSet::instance(),
            showOnHomepage: NotSet::instance(),
            showDate: NotSet::instance(),
            showAuthor: NotSet::instance(),
            showCategory: NotSet::instance(),
            showViews: NotSet::instance(),
            useGlobalSettings: NotSet::instance(),
            template: NotSet::instance(),
            metaTitle: NotSet::instance(),
            metaDescription: NotSet::instance(),
            metaKeywords: NotSet::instance(),
        );

        $updated = $material->update($data);

        $this->assertSame('Updated', $updated->title);
        $this->assertSame('original', $updated->slug);
    }

    public function test_publish_changes_status_and_sets_date(): void
    {
        $material = Material::create(
            title: 'Draft',
            userId: 1,
            slug: 'draft',
            status: MaterialStatus::DRAFT,
        );

        $published = $material->publish(new \DateTimeImmutable('2026-08-09 12:00:00'));

        $this->assertSame(MaterialStatus::PUBLISHED, $published->status);
        $this->assertSame('2026-08-09T12:00:00+00:00', $published->publishedAt->format(\DateTime::ATOM));
    }

    public function test_unpublish_resets_published_at(): void
    {
        $material = Material::create(
            title: 'Published',
            userId: 1,
            slug: 'published',
            status: MaterialStatus::PUBLISHED,
        );

        $unpublished = $material->unpublish(new \DateTimeImmutable('2026-08-09 12:00:00'));

        $this->assertSame(MaterialStatus::DRAFT, $unpublished->status);
        $this->assertNull($unpublished->publishedAt);
        $this->assertFalse($unpublished->showOnHomepage);
    }

    public function test_move_to_trash_sets_trash_status(): void
    {
        $material = Material::create(title: 'To Trash', userId: 1, slug: 'to-trash');

        $trashed = $material->moveToTrash(new \DateTimeImmutable('2026-08-09 12:00:00'));

        $this->assertSame(MaterialStatus::TRASH, $trashed->status);
        $this->assertNotNull($trashed->deletedAt);
        $this->assertFalse($trashed->showOnHomepage);
    }

    public function test_restore_sets_draft_status(): void
    {
        $material = Material::create(title: 'Trashed', userId: 1, slug: 'trashed');
        $material = $material->moveToTrash(new \DateTimeImmutable('2026-08-09 12:00:00'));

        $restored = $material->restore(new \DateTimeImmutable('2026-08-09 12:00:00'));

        $this->assertSame(MaterialStatus::DRAFT, $restored->status);
        $this->assertNull($restored->deletedAt);
        $this->assertNull($restored->publishedAt);
    }

    public function test_toggle_homepage_toggles_flag(): void
    {
        $material = Material::create(title: 'Test', userId: 1, slug: 'test');

        $toggled = $material->toggleHomepage(true, new \DateTimeImmutable('2026-08-09 12:00:00'));

        $this->assertTrue($toggled->showOnHomepage);
    }

    public function test_to_array_contains_expected_keys(): void
    {
        $material = Material::create(title: 'Array Test', userId: 1, slug: 'array-test');

        $array = $material->toArray();

        $this->assertArrayHasKey('id', $array);
        $this->assertArrayHasKey('title', $array);
        $this->assertArrayHasKey('slug', $array);
        $this->assertArrayHasKey('state', $array);
        $this->assertArrayHasKey('category', $array);
    }
}
