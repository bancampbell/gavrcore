<?php

namespace Tests\Unit\Modules\MediaManager\Application\UseCases;

use PHPUnit\Framework\TestCase;
use Modules\MediaManager\Application\DTO\DeleteItemsData;
use Modules\MediaManager\Application\DTO\OperationResult;
use Modules\MediaManager\Application\UseCases\DeleteItemsUseCase;
use Modules\MediaManager\Domain\Repositories\MediaRepositoryInterface;

class DeleteItemsUseCaseTest extends TestCase
{
    private $repository;
    private $useCase;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(MediaRepositoryInterface::class);
        $this->useCase = new DeleteItemsUseCase($this->repository);
    }

    public function test_execute_deletes_multiple_items_successfully(): void
    {
        $paths = ['folder1', 'folder2', 'file.txt'];
        $data = new DeleteItemsData(paths: $paths);

        $this->repository
            ->expects($this->exactly(3))
            ->method('exists')
            ->willReturn(true);

        $this->repository
            ->expects($this->exactly(3))
            ->method('delete');

        $result = $this->useCase->execute($data);

        $this->assertInstanceOf(OperationResult::class, $result);
        $this->assertTrue($result->success);
        $this->assertEquals('Удалено элементов: 3', $result->message);
        $this->assertEquals(['folder1', 'folder2', 'file.txt'], $result->data['deleted']);
    }

    public function test_execute_skips_non_existent_items(): void
    {
        $paths = ['exists', 'not-found', 'also-exists'];
        $data = new DeleteItemsData(paths: $paths);

        $this->repository
            ->method('exists')
            ->willReturnCallback(fn ($path) => $path !== 'not-found');

        $this->repository
            ->expects($this->exactly(2))
            ->method('delete');

        $result = $this->useCase->execute($data);

        $this->assertInstanceOf(OperationResult::class, $result);
        $this->assertFalse($result->success);
        $this->assertEquals(2, count($result->data['deleted']));
        $this->assertEquals(1, count($result->errors));
    }

    public function test_execute_returns_only_deleted_paths(): void
    {
        $paths = ['a', 'b', 'c'];
        $data = new DeleteItemsData(paths: $paths);

        $this->repository
            ->method('exists')
            ->willReturnCallback(fn ($path) => $path !== 'b');

        $this->repository
            ->expects($this->exactly(2))
            ->method('delete');

        $result = $this->useCase->execute($data);

        $this->assertInstanceOf(OperationResult::class, $result);
        $this->assertContains('a', $result->data['deleted']);
        $this->assertContains('c', $result->data['deleted']);
        $this->assertNotContains('b', $result->data['deleted']);
    }
}
