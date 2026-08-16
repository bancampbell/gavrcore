<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\GalleryManager\Application\UseCases;

use App\Modules\GalleryManager\Application\DTO\UpdateGalleryData;
use App\Modules\GalleryManager\Application\UseCases\UpdateGalleryUseCase;
use App\Modules\GalleryManager\Domain\Entities\Gallery;
use App\Modules\GalleryManager\Domain\Repositories\GalleryRepositoryInterface;
use App\Modules\GalleryManager\Domain\ValueObjects\GalleryStatus;
use App\Modules\GalleryManager\Domain\ValueObjects\GalleryType;
use PHPUnit\Framework\TestCase;
use Mockery;

class UpdateGalleryUseCaseTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_execute_updates_existing_gallery(): void
    {
        $repository = Mockery::mock(GalleryRepositoryInterface::class);
        $useCase = new UpdateGalleryUseCase($repository);

        $gallery = new Gallery(
            id: 1, title: 'Old', type: GalleryType::GRID,
            settings: [], status: GalleryStatus::DRAFT, ordering: 0,
        );

        $repository->shouldReceive('findById')
            ->with(1)
            ->once()
            ->andReturn($gallery);

        $repository->shouldReceive('update')
            ->once()
            ->with(Mockery::on(fn (Gallery $g) =>
                $g->id === 1
                && $g->title === 'Updated'
                && $g->status === GalleryStatus::PUBLISHED
            ));

        $data = new UpdateGalleryData(
            title: 'Updated',
            type: GalleryType::SLIDER,
            settings: ['new' => 'data'],
            status: true,
        );

        $useCase->execute(1, $data);

        $this->assertTrue(true);
    }

    public function test_execute_throws_exception_when_gallery_not_found(): void
    {
        $repository = Mockery::mock(GalleryRepositoryInterface::class);
        $useCase = new UpdateGalleryUseCase($repository);

        $repository->shouldReceive('findById')
            ->with(999)
            ->once()
            ->andReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Gallery not found');

        $data = new UpdateGalleryData(
            title: 'Updated',
            type: GalleryType::GRID,
            settings: [],
            status: true,
        );

        $useCase->execute(999, $data);
    }
}
