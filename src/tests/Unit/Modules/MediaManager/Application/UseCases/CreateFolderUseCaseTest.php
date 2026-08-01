<?php

namespace Tests\Unit\Modules\MediaManager\Application\UseCases;

use PHPUnit\Framework\TestCase;
use Modules\MediaManager\Application\DTO\CreateFolderData;
use Modules\MediaManager\Application\UseCases\CreateFolderUseCase;
use Modules\MediaManager\Domain\Repositories\MediaRepositoryInterface;

class CreateFolderUseCaseTest extends TestCase
{
    private $repository;
    private $useCase;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(MediaRepositoryInterface::class);
        $this->useCase = new CreateFolderUseCase($this->repository, 10);
    }

    public function test_execute_creates_folder_successfully(): void
    {
        $data = new CreateFolderData(name: 'new-folder', path: '');

        $this->repository
            ->expects($this->once())
            ->method('folderExists')
            ->with('new-folder')
            ->willReturn(false);

        $this->repository
            ->expects($this->once())
            ->method('createFolder')
            ->with('new-folder', '');

        $this->useCase->execute($data);
    }

    public function test_execute_throws_exception_when_folder_already_exists(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Папка с таким именем уже существует');

        $data = new CreateFolderData(name: 'existing-folder', path: '');

        $this->repository
            ->expects($this->once())
            ->method('folderExists')
            ->willReturn(true);

        $this->useCase->execute($data);
    }

    public function test_execute_creates_folder_in_subdirectory(): void
    {
        $data = new CreateFolderData(name: 'subfolder', path: 'parent');

        $this->repository
            ->expects($this->once())
            ->method('folderExists')
            ->with('parent/subfolder')
            ->willReturn(false);

        $this->repository
            ->expects($this->once())
            ->method('createFolder')
            ->with('subfolder', 'parent');

        $this->useCase->execute($data);
    }

    public function test_execute_throws_exception_when_max_depth_exceeded(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Превышена максимальная глубина вложенности');

        $useCase = new CreateFolderUseCase($this->repository, 2);

        $data = new CreateFolderData(name: 'deep', path: 'level1/level2');

        $this->repository->expects($this->never())->method('folderExists');

        $useCase->execute($data);
    }
}
