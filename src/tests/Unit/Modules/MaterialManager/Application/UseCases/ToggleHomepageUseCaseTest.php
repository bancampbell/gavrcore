<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\MaterialManager\Application\UseCases;

use App\Modules\MaterialManager\Application\UseCases\ToggleHomepageUseCase;
use App\Modules\MaterialManager\Domain\Entities\Material;
use App\Modules\MaterialManager\Domain\Repositories\MaterialRepositoryInterface;
use App\Modules\MaterialManager\Domain\Services\ClockInterface;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class ToggleHomepageUseCaseTest extends TestCase
{
    private MaterialRepositoryInterface&MockObject $repository;
    private ClockInterface&MockObject $clock;
    private ToggleHomepageUseCase $useCase;

    protected function setUp(): void
    {
        parent::setUp();
        Event::fake();

        $this->repository = $this->createMock(MaterialRepositoryInterface::class);
        $this->clock = $this->createMock(ClockInterface::class);
        $this->clock->method('now')->willReturn(new \DateTimeImmutable('2026-01-01 12:00:00'));

        $this->useCase = new ToggleHomepageUseCase($this->repository, $this->clock);
    }

    public function test_enables_homepage_and_clears_others(): void
    {
        $material = Material::create(title: 'Test', userId: 1, slug: 'test');

        $this->repository->expects($this->once())
            ->method('findByIdWithLock')
            ->with(1)
            ->willReturn($material);

        $this->repository->expects($this->once())->method('clearHomepageExcept')->with(1);

        $this->repository->expects($this->once())
            ->method('save')
            ->with($this->callback(fn ($m) => $m->showOnHomepage === true));

        $this->useCase->execute(1, true, 99);
    }

    public function test_disables_homepage_without_clearing_others(): void
    {
        $material = Material::create(title: 'Test', userId: 1, slug: 'test', showOnHomepage: true);

        $this->repository->expects($this->once())
            ->method('findByIdWithLock')
            ->with(1)
            ->willReturn($material);

        $this->repository->expects($this->never())->method('clearHomepageExcept');

        $this->repository->expects($this->once())
            ->method('save')
            ->with($this->callback(fn ($m) => $m->showOnHomepage === false));

        $this->useCase->execute(1, false, 99);
    }
}
