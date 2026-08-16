<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\GalleryManager\Domain\Entities;

use App\Modules\GalleryManager\Domain\Entities\Gallery;
use App\Modules\GalleryManager\Domain\Entities\GalleryImage;
use App\Modules\GalleryManager\Domain\Events\GalleryUpdated;
use App\Modules\GalleryManager\Domain\ValueObjects\GalleryStatus;
use App\Modules\GalleryManager\Domain\ValueObjects\GalleryType;
use PHPUnit\Framework\TestCase;

class GalleryTest extends TestCase
{
    public function test_create_factory_method_returns_gallery_with_null_id_and_zero_ordering(): void
    {
        $gallery = Gallery::create(
            title: 'Test Gallery',
            type: GalleryType::GRID,
            settings: ['foo' => 'bar'],
            status: GalleryStatus::DRAFT,
        );

        $this->assertNull($gallery->id);
        $this->assertEquals('Test Gallery', $gallery->title);
        $this->assertEquals(GalleryType::GRID, $gallery->type);
        $this->assertEquals(['foo' => 'bar'], $gallery->settings);
        $this->assertEquals(GalleryStatus::DRAFT, $gallery->status);
        $this->assertEquals(0, $gallery->ordering);
    }

    public function test_update_modifies_properties_and_records_event(): void
    {
        $gallery = new Gallery(
            id: 1,
            title: 'Old',
            type: GalleryType::GRID,
            settings: [],
            status: GalleryStatus::DRAFT,
            ordering: 0,
        );

        $gallery->update(
            title: 'New',
            type: GalleryType::SLIDER,
            settings: ['key' => 'val'],
            status: GalleryStatus::PUBLISHED,
        );

        $this->assertEquals('New', $gallery->title);
        $this->assertEquals(GalleryType::SLIDER, $gallery->type);
        $this->assertEquals(['key' => 'val'], $gallery->settings);
        $this->assertEquals(GalleryStatus::PUBLISHED, $gallery->status);

        $events = $gallery->releaseEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(GalleryUpdated::class, $events[0]);
        $this->assertEquals(1, $events[0]->getGalleryId());
    }

    public function test_publish_sets_status_to_published_and_records_event(): void
    {
        $gallery = new Gallery(
            id: 2,
            title: 'Test',
            type: GalleryType::GRID,
            settings: [],
            status: GalleryStatus::DRAFT,
            ordering: 0,
        );

        $gallery->publish();

        $this->assertEquals(GalleryStatus::PUBLISHED, $gallery->status);
        $events = $gallery->releaseEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(GalleryUpdated::class, $events[0]);
    }

    public function test_unpublish_sets_status_to_draft_and_records_event(): void
    {
        $gallery = new Gallery(
            id: 3,
            title: 'Test',
            type: GalleryType::GRID,
            settings: [],
            status: GalleryStatus::PUBLISHED,
            ordering: 0,
        );

        $gallery->unpublish();

        $this->assertEquals(GalleryStatus::DRAFT, $gallery->status);
        $events = $gallery->releaseEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(GalleryUpdated::class, $events[0]);
    }

    public function test_is_published_returns_correct_boolean(): void
    {
        $draft = new Gallery(
            id: 1, title: 'Draft', type: GalleryType::GRID,
            settings: [], status: GalleryStatus::DRAFT, ordering: 0
        );
        $published = new Gallery(
            id: 2, title: 'Published', type: GalleryType::GRID,
            settings: [], status: GalleryStatus::PUBLISHED, ordering: 0
        );

        $this->assertFalse($draft->isPublished());
        $this->assertTrue($published->isPublished());
    }

    public function test_add_image_and_images_methods(): void
    {
        $gallery = new Gallery(
            id: 1, title: 'Test', type: GalleryType::GRID,
            settings: [], status: GalleryStatus::PUBLISHED, ordering: 0
        );
        $image = new GalleryImage(
            id: 10, galleryId: 1, imagePath: '/test.jpg',
            title: null, description: null, altText: null,
            link: null, ordering: 1, status: true,
        );

        $gallery->addImage($image);

        $this->assertCount(1, $gallery->images());
        $this->assertSame($image, $gallery->images()[0]);
    }

    public function test_set_images_replaces_collection(): void
    {
        $gallery = new Gallery(
            id: 1, title: 'Test', type: GalleryType::GRID,
            settings: [], status: GalleryStatus::PUBLISHED, ordering: 0
        );
        $images = [
            new GalleryImage(
                id: 1, galleryId: 1, imagePath: '/a.jpg',
                title: null, description: null, altText: null,
                link: null, ordering: 0, status: true,
            ),
            new GalleryImage(
                id: 2, galleryId: 1, imagePath: '/b.jpg',
                title: null, description: null, altText: null,
                link: null, ordering: 1, status: true,
            ),
        ];

        $gallery->setImages($images);

        $this->assertCount(2, $gallery->images());
    }

    public function test_release_events_clears_domain_events(): void
    {
        $gallery = new Gallery(
            id: 1, title: 'Test', type: GalleryType::GRID,
            settings: [], status: GalleryStatus::DRAFT, ordering: 0
        );
        $gallery->publish();

        $first = $gallery->releaseEvents();
        $second = $gallery->releaseEvents();

        $this->assertCount(1, $first);
        $this->assertCount(0, $second);
    }

    public function test_to_array_returns_expected_structure(): void
    {
        $gallery = new Gallery(
            id: 1,
            title: 'Test',
            type: GalleryType::GRID,
            settings: ['k' => 'v'],
            status: GalleryStatus::PUBLISHED,
            ordering: 5,
            createdAt: '2024-01-01 00:00:00',
            updatedAt: '2024-01-02 00:00:00',
            imagesCount: 3,
        );

        $array = $gallery->toArray();

        $this->assertEquals([
            'id' => 1,
            'title' => 'Test',
            'type' => 'grid',
            'settings' => ['k' => 'v'],
            'status' => 1,
            'ordering' => 5,
            'images' => [],
            'images_count' => 3,
            'created_at' => '2024-01-01 00:00:00',
            'updated_at' => '2024-01-02 00:00:00',
        ], $array);
    }
}
