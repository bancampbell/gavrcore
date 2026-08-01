<?php

namespace Tests\Unit\Modules\MediaManager\Domain\Entities;

use Tests\TestCase;
use Modules\MediaManager\Domain\Entities\Media;
use Modules\MediaManager\Domain\ValueObjects\MediaPath;

class MediaTest extends TestCase
{
    public function test_media_creation(): void
    {
        $media = new Media(
            id: 1,
            name: 'test-file.jpg',
            path: new MediaPath('uploads/test-file.jpg'),
            type: 'file',
            size: 1024,
            mimeType: 'image/jpeg',
            parentId: null,
            createdAt: 1234567890,
            updatedAt: 1234567890
        );

        $this->assertEquals(1, $media->getId());
        $this->assertEquals('test-file.jpg', $media->getName());
        $this->assertEquals('uploads/test-file.jpg', $media->getPath()->toString());
        $this->assertEquals('file', $media->getType());
        $this->assertEquals(1024, $media->getSize());
        $this->assertEquals('image/jpeg', $media->getMimeType());
        $this->assertNull($media->getParentId());
        $this->assertEquals(1234567890, $media->getCreatedAt());
        $this->assertEquals(1234567890, $media->getUpdatedAt());
    }

    public function test_is_folder_returns_true_for_folder(): void
    {
        $media = new Media(
            id: null,
            name: 'folder',
            path: new MediaPath('folder'),
            type: 'folder',
            size: null,
            mimeType: null,
            parentId: null,
            createdAt: null,
            updatedAt: null
        );

        $this->assertTrue($media->isFolder());
        $this->assertFalse($media->isFile());
    }

    public function test_is_file_returns_true_for_file(): void
    {
        $media = new Media(
            id: null,
            name: 'file.txt',
            path: new MediaPath('file.txt'),
            type: 'file',
            size: 100,
            mimeType: 'text/plain',
            parentId: null,
            createdAt: null,
            updatedAt: null
        );

        $this->assertTrue($media->isFile());
        $this->assertFalse($media->isFolder());
    }

    public function test_to_array_returns_correct_array(): void
    {
        $media = new Media(
            id: 1,
            name: 'file.txt',
            path: new MediaPath('uploads/file.txt'),
            type: 'file',
            size: 100,
            mimeType: 'text/plain',
            parentId: 5,
            createdAt: 1234567890,
            updatedAt: 1234567890
        );

        $array = $media->toArray();

        $this->assertEquals([
            'id' => 1,
            'name' => 'file.txt',
            'path' => 'uploads/file.txt',
            'type' => 'file',
            'size' => 100,
            'mime_type' => 'text/plain',
            'parent_id' => 5,
            'created_at' => 1234567890,
            'updated_at' => 1234567890,
        ], $array);
    }

    public function test_json_serialize(): void
    {
        $media = new Media(
            id: 1,
            name: 'file.txt',
            path: new MediaPath('file.txt'),
            type: 'file',
            size: 100,
            mimeType: 'text/plain',
            parentId: null,
            createdAt: null,
            updatedAt: null
        );

        $json = json_encode($media);
        $this->assertJson($json);
        $this->assertStringContainsString('"name":"file.txt"', $json);
    }
}
