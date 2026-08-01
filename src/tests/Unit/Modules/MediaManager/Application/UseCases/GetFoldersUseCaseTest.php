<?php

namespace Tests\Unit\Modules\MediaManager\Application\UseCases;

use Tests\TestCase;
use Modules\MediaManager\Application\UseCases\GetFoldersUseCase;
use Modules\MediaManager\Domain\Repositories\MediaRepositoryInterface;
use Modules\MediaManager\Domain\Entities\Media;
use Modules\MediaManager\Domain\ValueObjects\MediaPath;

class GetFoldersUseCaseTest extends TestCase
{
    private GetFoldersUseCase $useCase;
    private $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(MediaRepositoryInterface::class);
        $this->useCase = new GetFoldersUseCase($this->repository);
    }

    public function test_execute_returns_root_folders_successfully(): void
    {
        $expectedFolders = [
            new Media(
                id: null,
                name: '2025',
                path: new MediaPath('2025'),
                type: 'folder',
                size: null,
                mimeType: null,
                parentId: null,
                createdAt: null,
                updatedAt: null
            ),
            new Media(
                id: null,
                name: '2026',
                path: new MediaPath('2026'),
                type: 'folder',
                size: null,
                mimeType: null,
                parentId: null,
                createdAt: null,
                updatedAt: null
            ),
        ];

        $this->repository
            ->expects($this->once())
            ->method('getRootFolders')
            ->willReturn($expectedFolders);

        $result = $this->useCase->execute();
        $this->assertCount(2, $result);
        $this->assertEquals($expectedFolders, $result);
    }

    public function test_execute_returns_empty_array_when_no_folders(): void
    {
        $this->repository
            ->expects($this->once())
            ->method('getRootFolders')
            ->willReturn([]);

        $result = $this->useCase->execute();
        $this->assertEmpty($result);
    }

    public function test_execute_returns_only_folders_not_files(): void
    {
        $folders = [
            new Media(
                id: null,
                name: 'folder1',
                path: new MediaPath('folder1'),
                type: 'folder',
                size: null,
                mimeType: null,
                parentId: null,
                createdAt: null,
                updatedAt: null
            ),
            new Media(
                id: null,
                name: 'folder2',
                path: new MediaPath('folder2'),
                type: 'folder',
                size: null,
                mimeType: null,
                parentId: null,
                createdAt: null,
                updatedAt: null
            ),
        ];

        $this->repository
            ->expects($this->once())
            ->method('getRootFolders')
            ->willReturn($folders);

        $result = $this->useCase->execute();
        $this->assertCount(2, $result);
        foreach ($result as $folder) {
            $this->assertEquals('folder', $folder->getType());
        }
    }

    public function test_execute_returns_folders_with_correct_names(): void
    {
        $folderNames = ['Documents', 'Images', 'Videos'];
        $folders = array_map(function ($name) {
            return new Media(
                id: null,
                name: $name,
                path: new MediaPath($name),
                type: 'folder',
                size: null,
                mimeType: null,
                parentId: null,
                createdAt: null,
                updatedAt: null
            );
        }, $folderNames);

        $this->repository
            ->expects($this->once())
            ->method('getRootFolders')
            ->willReturn($folders);

        $result = $this->useCase->execute();
        $this->assertCount(3, $result);
        $this->assertEquals('Documents', $result[0]->getName());
        $this->assertEquals('Images', $result[1]->getName());
        $this->assertEquals('Videos', $result[2]->getName());
    }
}
