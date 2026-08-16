<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\GalleryManager\Application\UseCases;

use App\Modules\GalleryManager\Application\DTO\CreateGalleryData;
use App\Modules\GalleryManager\Application\UseCases\CreateGalleryUseCase;
use App\Modules\GalleryManager\Domain\Entities\Gallery;
use App\Modules\GalleryManager\Domain\Repositories\GalleryRepositoryInterface;
use App\Modules\GalleryManager\Domain\ValueObjects\GalleryStatus;
use App\Modules\GalleryManager\Domain\ValueObjects\GalleryType;
use PHPUnit\Framework\TestCase;
use Mockery;

class CreateGalleryUseCaseTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_execute_creates_gallery_and_returns_persisted_entity(): void
    {
        $repository = Mockery::mock(GalleryRepositoryInterface::class);
        $useCase = new CreateGalleryUseCase($repository);

        $data = new CreateGalleryData(
            title: 'New Gallery',
            type: GalleryType::GRID,
            settings: ['key' => 'value'],
            status: true,
        );

        $repository->shouldReceive('create')
            ->once()
            ->with(Mockery::on(fn (Gallery $gallery) =>
                $gallery->title === 'New Gallery'
                && $gallery->type === GalleryType::GRID
                && $gallery->status === GalleryStatus::PUBLISHED
            ))
            ->andReturnUsing(function (Gallery $gallery) {
                return new Gallery(
                    id: 1,
                    title: $gallery->title,
                    type: $gallery->type,
                    settings: $gallery->settings,
                    status: $gallery->status,
                    ordering: $gallery->ordering,
                );
            });

        $result = $useCase->execute($data);

        $this->assertInstanceOf(Gallery::class, $result);
        $this->assertEquals(1, $result->id);
        $this->assertEquals('New Gallery', $result->title);
        $this->assertEquals(GalleryStatus::PUBLISHED, $result->status);
    }

    public function test_execute_creates_draft_gallery_when_status_false(): void
    {
        $repository = Mockery::mock(GalleryRepositoryInterface::class);
        $useCase = new CreateGalleryUseCase($repository);

        $data = new CreateGalleryData(
            title: 'Draft',
            type: GalleryType::SLIDER,
            settings: [],
            status: false,
        );

        $repository->shouldReceive('create')
            ->once()
            ->with(Mockery::on(fn (Gallery $gallery) =>
                $gallery->status === GalleryStatus::DRAFT
            ))
            ->andReturnUsing(fn (Gallery $g) => new Gallery(
                id: 2, title: $g->title, type: $g->type,
                settings: $g->settings, status: $g->status, ordering: 0
            ));

        $result = $useCase->execute($data);

        $this->assertEquals(GalleryStatus::DRAFT, $result->status);
    }
}
