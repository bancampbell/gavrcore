<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\MaterialManager\Application\UseCases;

use App\Modules\MaterialManager\Application\UseCases\DeleteMaterialUseCase;
use App\Modules\MaterialManager\Domain\Entities\Material;
use App\Modules\MaterialManager\Domain\Repositories\MaterialRepositoryInterface;
use App\Modules\MaterialManager\Domain\Services\ClockInterface;
use App\Modules\MaterialManager\Domain\ValueObjects\MaterialStatus;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class DeleteMaterialUseCaseTest extends TestCase
{
    private MaterialRepositoryInterface&MockObject $repository;
    private ClockInterface&MockObject $clock;
    private DeleteMaterialUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();

        $this->repository = $this->createMock(MaterialRepositoryInterface::class);
        $this->clock = $this->createMock(ClockInterface::class);
        $this->clock->method('now')->willReturn(new \DateTimeImmutable('2026-01-01 12:00:00'));

        $this->useCase = new DeleteMaterialUseCase($this->repository, $this->clock);
    }

    public function test_moves_material_to_trash(): void
    {
        $material = Material::create(
            title: 'To Delete',
            userId: 1,
            slug: 'to-delete',
            status: MaterialStatus::PUBLISHED,
        );

        $this->repository->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($material);

        $this->repository->expects($this->once())
            ->method('delete')
            ->with($this->callback(fn ($m) => $m->status === MaterialStatus::TRASH));

        $this->useCase->execute(1, 99);
    }

    public function test_throws_when_material_not_found(): void
    {
        $this->repository->method('findById')->willReturn(null);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Material not found');

        $this->useCase->execute(1, 99);
    }
}
