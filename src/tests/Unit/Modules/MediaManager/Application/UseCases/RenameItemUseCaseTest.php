<?php

namespace Tests\Unit\Modules\MediaManager\Application\UseCases;

use PHPUnit\Framework\TestCase;
use Modules\MediaManager\Application\DTO\RenameItemData;
use Modules\MediaManager\Application\UseCases\RenameItemUseCase;
use Modules\MediaManager\Domain\Repositories\MediaRepositoryInterface;

class RenameItemUseCaseTest extends TestCase
{
    public function test_execute_renames_item_successfully(): void
    {
        $repo = $this->createMock(MediaRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('exists')
            ->with('old-folder')
            ->willReturn(true);
        $repo->expects($this->once())
            ->method('rename')
            ->with('old-folder', 'new-folder');

        $useCase = new RenameItemUseCase($repo);
        $data = new RenameItemData(oldPath: 'old-folder', newName: 'new-folder');

        $useCase->execute($data);
    }

    public function test_execute_throws_exception_when_item_not_found(): void
    {
        $this->expectException(\RuntimeException::class);

        $repo = $this->createMock(MediaRepositoryInterface::class);
        $repo->method('exists')->willReturn(false);

        $useCase = new RenameItemUseCase($repo);
        $data = new RenameItemData(oldPath: 'missing', newName: 'new-name');

        $useCase->execute($data);
    }

    public function test_execute_renames_item_with_path_containing_slashes(): void
    {
        $repo = $this->createMock(MediaRepositoryInterface::class);
        $repo->expects($this->once())
            ->method('exists')
            ->with('folder/old.txt')
            ->willReturn(true);
        $repo->expects($this->once())
            ->method('rename')
            ->with('folder/old.txt', 'new.txt');

        $useCase = new RenameItemUseCase($repo);
        $data = new RenameItemData(oldPath: 'folder/old.txt', newName: 'new.txt');

        $useCase->execute($data);
    }
}
