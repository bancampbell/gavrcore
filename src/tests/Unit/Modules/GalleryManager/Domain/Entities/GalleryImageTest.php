<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\GalleryManager\Domain\Entities;

use App\Modules\GalleryManager\Domain\Entities\GalleryImage;
use PHPUnit\Framework\TestCase;

class GalleryImageTest extends TestCase
{
    public function test_constructor_sets_all_properties(): void
    {
        $image = new GalleryImage(
            id: 1,
            galleryId: 10,
            imagePath: '/storage/test.jpg',
            title: 'Title',
            description: 'Description',
            altText: 'Alt',
            link: 'https://example.com',
            ordering: 5,
            status: true,
            createdAt: '2024-01-01',
            updatedAt: '2024-01-02',
        );

        $this->assertEquals(1, $image->id);
        $this->assertEquals(10, $image->galleryId);
        $this->assertEquals('/storage/test.jpg', $image->imagePath);
        $this->assertEquals('Title', $image->title);
        $this->assertEquals('Description', $image->description);
        $this->assertEquals('Alt', $image->altText);
        $this->assertEquals('https://example.com', $image->link);
        $this->assertEquals(5, $image->ordering);
        $this->assertTrue($image->status);
    }

    public function test_update_meta_updates_fields(): void
    {
        $image = new GalleryImage(
            id: 1, galleryId: 10, imagePath: '/test.jpg',
            title: 'Old', description: 'Old Desc',
            altText: 'Old Alt', link: 'https://old.com',
            ordering: 0, status: true,
        );

        $image->updateMeta('New', 'New Desc', 'New Alt', 'https://new.com');

        $this->assertEquals('New', $image->title);
        $this->assertEquals('New Desc', $image->description);
        $this->assertEquals('New Alt', $image->altText);
        $this->assertEquals('https://new.com', $image->link);
    }

    public function test_to_array_returns_expected_structure(): void
    {
        $image = new GalleryImage(
            id: 1, galleryId: 10, imagePath: '/test.jpg',
            title: 'Title', description: null, altText: 'Alt',
            link: null, ordering: 2, status: false,
            createdAt: '2024-01-01', updatedAt: '2024-01-02',
        );

        $this->assertEquals([
            'id' => 1,
            'gallery_id' => 10,
            'image_path' => '/test.jpg',
            'title' => 'Title',
            'description' => null,
            'alt_text' => 'Alt',
            'link' => null,
            'ordering' => 2,
            'status' => false,
            'created_at' => '2024-01-01',
            'updated_at' => '2024-01-02',
        ], $image->toArray());
    }
}
