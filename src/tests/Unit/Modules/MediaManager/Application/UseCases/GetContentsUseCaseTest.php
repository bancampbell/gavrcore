<?php

namespace Tests\Unit\Modules\MediaManager\Application\UseCases;

use Tests\TestCase;
use Modules\MediaManager\Application\UseCases\GetContentsUseCase;
use Modules\MediaManager\Domain\Repositories\MediaRepositoryInterface;
use Modules\MediaManager\Domain\Entities\Media;
use Modules\MediaManager\Domain\ValueObjects\MediaPath;

class GetContentsUseCaseTest extends TestCase
{
    private GetContentsUseCase $useCase;
    private $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repository = $this->createMock(MediaRepositoryInterface::class);
        $this->useCase = new GetContentsUseCase($this->repository);
    }

    public function test_execute_returns_contents_successfully(): void
    {
        $path = 'folder';
        $expectedContents = [
            new Media(
                id: null,
                name: 'subfolder',
                path: new MediaPath('folder/subfolder'),
                type: 'folder',
                size: null,
                mimeType: null,
                parentId: null,
                createdAt: null,
                updatedAt: null
            ),
            new Media(
                id: null,
                name: 'file.txt',
                path: new MediaPath('folder/file.txt'),
                type: 'file',
                size: 1024,
                mimeType: 'text/plain',
                parentId: null,
                createdAt: null,
                updatedAt: null
            ),
        ];

        $this->repository
            ->expects($this->once())
            ->method('getContents')
            ->with($path)
            ->willReturn($expectedContents);

        $result = $this->useCase->execute($path);
        $this->assertCount(2, $result);
        $this->assertEquals($expectedContents, $result);
    }

    public function test_execute_returns_empty_array_when_folder_empty(): void
    {
        $path = 'empty-folder';

        $this->repository
            ->expects($this->once())
            ->method('getContents')
            ->with($path)
            ->willReturn([]);

        $result = $this->useCase->execute($path);
        $this->assertEmpty($result);
    }

    public function test_execute_returns_contents_from_root(): void
    {
        $path = '';
        $expectedContents = [
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
            ->method('getContents')
            ->with('')
            ->willReturn($expectedContents);

        $result = $this->useCase->execute('');
        $this->assertCount(2, $result);
    }

    public function test_execute_returns_only_files_and_folders(): void
    {
        $path = 'mixed';

        $this->repository
            ->expects($this->once())
            ->method('getContents')
            ->with($path)
            ->willReturn([
                new Media(
                    id: null,
                    name: 'folder',
                    path: new MediaPath('mixed/folder'),
                    type: 'folder',
                    size: null,
                    mimeType: null,
                    parentId: null,
                    createdAt: null,
                    updatedAt: null
                ),
                new Media(
                    id: null,
                    name: 'file.txt',
                    path: new MediaPath('mixed/file.txt'),
                    type: 'file',
                    size: 512,
                    mimeType: 'text/plain',
                    parentId: null,
                    createdAt: null,
                    updatedAt: null
                ),
            ]);

        $result = $this->useCase->execute($path);
        $this->assertCount(2, $result);
        $this->assertEquals('folder', $result[0]->getName());
        $this->assertEquals('file.txt', $result[1]->getName());
    }
}
