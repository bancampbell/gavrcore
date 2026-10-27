<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\MaterialManager\Application\UseCases;

use App\Modules\MaterialManager\Application\Services\HomepageManager;
use App\Modules\MaterialManager\Application\UseCases\BulkDeleteUseCase;
use App\Modules\MaterialManager\Domain\Repositories\MaterialRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class BulkDeleteUseCaseTest extends TestCase
{
    private MaterialRepositoryInterface&MockObject $repository;

    private HomepageManager&MockObject $homepageManager;

    private BulkDeleteUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = $this->createMock(MaterialRepositoryInterface::class);
        $this->homepageManager = $this->createMock(HomepageManager::class);
        $this->useCase = new BulkDeleteUseCase($this->repository, $this->homepageManager);
    }

    public function test_delegates_to_repository(): void
    {
        $this->homepageManager->method('currentMaterialId')->willReturn(null);
        $this->repository->expects($this->once())
            ->method('bulkDelete')
            ->with([1, 2, 3])
            ->willReturn(3);
        $this->homepageManager->expects($this->never())->method('unassign');

        $result = $this->useCase->execute([1, 2, 3], 99);

        $this->assertSame(3, $result);
    }

    public function test_unassigns_homepage_when_homepage_material_trashed(): void
    {
        $this->homepageManager->method('currentMaterialId')->willReturn(2);
        $this->repository->method('bulkDelete')->willReturn(3);
        $this->homepageManager->expects($this->once())->method('unassign');

        $this->useCase->execute([1, 2, 3], 99);
    }

    public function test_keeps_homepage_when_other_materials_trashed(): void
    {
        $this->homepageManager->method('currentMaterialId')->willReturn(9);
        $this->repository->method('bulkDelete')->willReturn(2);
        $this->homepageManager->expects($this->never())->method('unassign');

        $this->useCase->execute([1, 2], 99);
    }
}
