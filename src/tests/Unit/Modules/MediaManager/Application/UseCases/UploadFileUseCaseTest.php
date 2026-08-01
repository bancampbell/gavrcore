<?php

namespace Tests\Unit\Modules\MediaManager\Application\UseCases;

use PHPUnit\Framework\TestCase;
use Modules\MediaManager\Application\DTO\UploadFileData;
use Modules\MediaManager\Application\DTO\OperationResult;
use Modules\MediaManager\Application\UseCases\UploadFileUseCase;
use Modules\MediaManager\Domain\Repositories\MediaRepositoryInterface;

class UploadFileUseCaseTest extends TestCase
{
    public function test_execute_uploads_files_successfully(): void
    {
        $repo = $this->createMock(MediaRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('folderExists')
            ->with('uploads')
            ->willReturn(true);
        $repo->expects($this->once())
            ->method('uploadFromPaths')
            ->with(['/tmp/file1.jpg', '/tmp/file2.png'], 'uploads')
            ->willReturn(['file1.jpg', 'file2.png']);

        $useCase = new UploadFileUseCase($repo);
        $data = new UploadFileData(filePaths: ['/tmp/file1.jpg', '/tmp/file2.png'], path: 'uploads');

        $result = $useCase->execute($data);

        $this->assertInstanceOf(OperationResult::class, $result);
        $this->assertTrue($result->success);
        $this->assertStringContainsString('2', $result->message);
    }

    public function test_execute_throws_exception_when_folder_not_exists(): void
    {
        $this->expectException(\RuntimeException::class);

        $repo = $this->createMock(MediaRepositoryInterface::class);
        $repo->method('folderExists')->willReturn(false);

        $useCase = new UploadFileUseCase($repo);
        $data = new UploadFileData(filePaths: ['/tmp/file.jpg'], path: 'missing');

        $useCase->execute($data);
    }

    public function test_execute_uploads_multiple_files(): void
    {
        $repo = $this->createMock(MediaRepositoryInterface::class);
        $repo->method('folderExists')->willReturn(true);
        $repo->method('uploadFromPaths')
            ->willReturn(['1.jpg', '2.jpg', '3.jpg']);

        $useCase = new UploadFileUseCase($repo);
        $data = new UploadFileData(filePaths: ['a', 'b', 'c'], path: 'gallery');

        $result = $useCase->execute($data);

        $this->assertInstanceOf(OperationResult::class, $result);
        $this->assertTrue($result->success);
        $this->assertCount(3, $result->data['uploaded'] ?? []);
    }

    public function test_execute_uploads_files_to_root(): void
    {
        $repo = $this->createMock(MediaRepositoryInterface::class);
        $repo->method('folderExists')->willReturn(true);
        $repo->method('uploadFromPaths')
            ->with(['/tmp/file1.jpg'], '')
            ->willReturn(['file1.jpg']);

        $useCase = new UploadFileUseCase($repo);
        $data = new UploadFileData(filePaths: ['/tmp/file1.jpg'], path: '');

        $result = $useCase->execute($data);

        $this->assertInstanceOf(OperationResult::class, $result);
        $this->assertTrue($result->success);
    }
}
