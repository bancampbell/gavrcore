<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\MaterialManager\Application\UseCases;

use App\Modules\MaterialManager\Application\UseCases\PublishMaterialUseCase;
use App\Modules\MaterialManager\Domain\Entities\Material;
use App\Modules\MaterialManager\Domain\Repositories\MaterialRepositoryInterface;
use App\Modules\MaterialManager\Domain\Services\ClockInterface;
use App\Modules\MaterialManager\Domain\ValueObjects\MaterialStatus;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class PublishMaterialUseCaseTest extends TestCase
{
    private MaterialRepositoryInterface&MockObject $repository;
    private ClockInterface&MockObject $clock;
    private PublishMaterialUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();

        $this->repository = $this->createMock(MaterialRepositoryInterface::class);
        $this->clock = $this->createMock(ClockInterface::class);
        $this->clock->method('now')->willReturn(new \DateTimeImmutable('2026-01-01 12:00:00'));

        $this->useCase = new PublishMaterialUseCase($this->repository, $this->clock);
    }

    public function test_publishes_draft_material(): void
    {
        $material = Material::create(
            title: 'Draft',
            userId: 1,
            slug: 'draft',
            status: MaterialStatus::DRAFT,
        );

        $this->repository->expects($this->once())
            ->method('findById')
            ->with(1)
            ->willReturn($material);

        $this->repository->expects($this->once())
            ->method('save')
            ->with($this->callback(fn ($m) => $m->status === MaterialStatus::PUBLISHED && $m->publishedAt !== null));

        $this->useCase->execute(1, 99);
    }

    public function test_throws_when_publishing_already_published(): void
    {
        $material = Material::create(
            title: 'Published',
            userId: 1,
            slug: 'published',
            status: MaterialStatus::PUBLISHED,
        );

        $this->repository->method('findById')->willReturn($material);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Cannot publish material with status: published');

        $this->useCase->execute(1, 99);
    }
}
