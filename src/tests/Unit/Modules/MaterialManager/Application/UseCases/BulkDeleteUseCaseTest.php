<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\MaterialManager\Application\UseCases;

use App\Modules\MaterialManager\Application\UseCases\BulkDeleteUseCase;
use App\Modules\MaterialManager\Domain\Repositories\MaterialRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class BulkDeleteUseCaseTest extends TestCase
{
    private MaterialRepositoryInterface&MockObject $repository;
    private BulkDeleteUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->createMock(MaterialRepositoryInterface::class);
        $this->useCase = new BulkDeleteUseCase($this->repository);
    }

    public function test_delegates_to_repository(): void
    {
        $this->repository->expects($this->once())
            ->method('bulkDelete')
            ->with([1, 2, 3])
            ->willReturn(3);

        $result = $this->useCase->execute([1, 2, 3], 99);

        $this->assertSame(3, $result);
    }
}
