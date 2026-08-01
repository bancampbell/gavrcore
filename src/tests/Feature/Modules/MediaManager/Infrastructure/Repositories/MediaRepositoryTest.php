<?php

namespace Tests\Feature\Modules\MediaManager\Infrastructure\Repositories;

use Tests\TestCase;
use Illuminate\Support\Facades\Storage;
use Modules\MediaManager\Infrastructure\Repositories\MediaRepository;

class MediaRepositoryTest extends TestCase
{
    private MediaRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->repository = new MediaRepository();
    }

    public function test_create_folder_and_find_it(): void
    {
        $this->repository->createFolder('test-folder', '');

        $this->assertTrue($this->repository->folderExists('test-folder'));
        $this->assertTrue($this->repository->exists('test-folder'));
    }

    public function test_create_nested_folder(): void
    {
        $this->repository->createFolder('parent', '');
        $this->repository->createFolder('child', 'parent');

        $this->assertTrue($this->repository->folderExists('parent/child'));
    }

    public function test_rename_folder(): void
    {
        $this->repository->createFolder('old-name', '');
        $this->repository->rename('old-name', 'new-name');

        $this->assertFalse($this->repository->exists('old-name'));
        $this->assertTrue($this->repository->exists('new-name'));
    }

    public function test_delete_folder(): void
    {
        $this->repository->createFolder('to-delete', '');
        $this->repository->delete('to-delete');

        $this->assertFalse($this->repository->exists('to-delete'));
    }

    public function test_upload_file(): void
    {
        Storage::disk('public')->makeDirectory('uploads');
        $tempFile = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tempFile, 'test content');

        $result = $this->repository->uploadFromPaths([$tempFile], '');

        $this->assertNotEmpty($result);
        $this->assertTrue($this->repository->exists(basename($result[0])));

        unlink($tempFile);
    }

    public function test_resolve_path_blocks_traversal(): void
    {
        $this->expectException(\RuntimeException::class);

        // Доступ к приватному методу через Reflection
        $method = new \ReflectionMethod($this->repository, 'resolvePath');
        $method->setAccessible(true);
        $method->invoke($this->repository, '../secret');
    }

    public function test_resolve_path_blocks_traversal_in_middle(): void
    {
        $this->expectException(\RuntimeException::class);

        $method = new \ReflectionMethod($this->repository, 'resolvePath');
        $method->setAccessible(true);
        $method->invoke($this->repository, 'folder/../../etc/passwd');
    }

    public function test_get_contents_returns_folders_and_files(): void
    {
        $this->repository->createFolder('my-folder', '');
        Storage::disk('public')->put('uploads/my-file.txt', 'content');

        $contents = $this->repository->getContents('');

        $names = array_map(fn ($item) => $item->getName(), $contents);

        $this->assertContains('my-folder', $names);
        $this->assertContains('my-file.txt', $names);
    }

    public function test_copy_file(): void
    {
        Storage::disk('public')->put('uploads/original.txt', 'content');

        $this->repository->copy('original.txt');

        $this->assertTrue($this->repository->exists('original_copy_1.txt'));
    }

    public function test_paginated_contents_returns_structure(): void
    {
        Storage::disk('public')->put('uploads/file1.txt', 'a');
        Storage::disk('public')->put('uploads/file2.txt', 'b');

        $result = $this->repository->getPaginatedContents('', 1, 20, 'name_asc', null);

        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('page', $result);
        $this->assertArrayHasKey('per_page', $result);
        $this->assertArrayHasKey('last_page', $result);
        $this->assertGreaterThanOrEqual(2, $result['total']);
    }
}
