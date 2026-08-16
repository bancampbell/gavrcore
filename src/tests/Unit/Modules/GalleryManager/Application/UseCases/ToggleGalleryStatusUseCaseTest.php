<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\GalleryManager\Application\UseCases;

use App\Modules\GalleryManager\Application\UseCases\ToggleGalleryStatusUseCase;
use App\Modules\GalleryManager\Domain\Entities\Gallery;
use App\Modules\GalleryManager\Domain\Repositories\GalleryRepositoryInterface;
use App\Modules\GalleryManager\Domain\ValueObjects\GalleryStatus;
use App\Modules\GalleryManager\Domain\ValueObjects\GalleryType;
use PHPUnit\Framework\TestCase;
use Mockery;

class ToggleGalleryStatusUseCaseTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_execute_publishes_gallery(): void
    {
        $repository = Mockery::mock(GalleryRepositoryInterface::class);
        $useCase = new ToggleGalleryStatusUseCase($repository);

        $gallery = new Gallery(
            id: 1, title: 'Test', type: GalleryType::GRID,
            settings: [], status: GalleryStatus::DRAFT, ordering: 0,
        );

        $repository->shouldReceive('findById')->with(1)->once()->andReturn($gallery);
        $repository->shouldReceive('update')->once();

        $useCase->execute(1, true);

        $this->assertEquals(GalleryStatus::PUBLISHED, $gallery->status);
    }

    public function test_execute_unpublishes_gallery(): void
    {
        $repository = Mockery::mock(GalleryRepositoryInterface::class);
        $useCase = new ToggleGalleryStatusUseCase($repository);

        $gallery = new Gallery(
            id: 1, title: 'Test', type: GalleryType::GRID,
            settings: [], status: GalleryStatus::PUBLISHED, ordering: 0,
        );

        $repository->shouldReceive('findById')->with(1)->once()->andReturn($gallery);
        $repository->shouldReceive('update')->once();

        $useCase->execute(1, false);

        $this->assertEquals(GalleryStatus::DRAFT, $gallery->status);
    }

    public function test_execute_throws_when_gallery_not_found(): void
    {
        $repository = Mockery::mock(GalleryRepositoryInterface::class);
        $useCase = new ToggleGalleryStatusUseCase($repository);

        $repository->shouldReceive('findById')->with(999)->once()->andReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Gallery not found');

        $useCase->execute(999, true);
    }
}
