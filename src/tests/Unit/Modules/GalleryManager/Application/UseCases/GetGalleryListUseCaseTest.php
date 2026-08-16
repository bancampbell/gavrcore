<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\GalleryManager\Application\UseCases;

use App\Modules\GalleryManager\Application\DTO\GalleryFiltersData;
use App\Modules\GalleryManager\Application\UseCases\GetGalleryListUseCase;
use App\Modules\GalleryManager\Domain\Entities\Gallery;
use App\Modules\GalleryManager\Domain\Repositories\GalleryRepositoryInterface;
use App\Modules\GalleryManager\Domain\ValueObjects\GalleryStatus;
use App\Modules\GalleryManager\Domain\ValueObjects\GalleryType;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;
use Mockery;

class GetGalleryListUseCaseTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_execute_returns_all_galleries_without_filters(): void
    {
        $repository = Mockery::mock(GalleryRepositoryInterface::class);
        $useCase = new GetGalleryListUseCase($repository);

        $galleries = new Collection([
            new Gallery(id: 1, title: 'A', type: GalleryType::GRID, settings: [], status: GalleryStatus::PUBLISHED, ordering: 0, imagesCount: 0),
            new Gallery(id: 2, title: 'B', type: GalleryType::SLIDER, settings: [], status: GalleryStatus::DRAFT, ordering: 0, imagesCount: 0),
        ]);

        $repository->shouldReceive('getAllWithImageCount')->once()->andReturn($galleries);

        $result = $useCase->execute();

        $this->assertCount(2, $result);
    }

    public function test_execute_filters_by_search_term(): void
    {
        $repository = Mockery::mock(GalleryRepositoryInterface::class);
        $useCase = new GetGalleryListUseCase($repository);

        $galleries = new Collection([
            new Gallery(id: 1, title: 'Alpha', type: GalleryType::GRID, settings: [], status: GalleryStatus::PUBLISHED, ordering: 0, imagesCount: 0),
            new Gallery(id: 2, title: 'Beta', type: GalleryType::GRID, settings: [], status: GalleryStatus::PUBLISHED, ordering: 0, imagesCount: 0),
        ]);

        $repository->shouldReceive('getAllWithImageCount')->once()->andReturn($galleries);

        $filters = new GalleryFiltersData(search: 'Alpha');
        $result = $useCase->execute($filters);

        $this->assertCount(1, $result);
        $this->assertEquals('Alpha', $result->first()->title);
    }

    public function test_execute_filters_by_type(): void
    {
        $repository = Mockery::mock(GalleryRepositoryInterface::class);
        $useCase = new GetGalleryListUseCase($repository);

        $galleries = new Collection([
            new Gallery(id: 1, title: 'A', type: GalleryType::GRID, settings: [], status: GalleryStatus::PUBLISHED, ordering: 0, imagesCount: 0),
            new Gallery(id: 2, title: 'B', type: GalleryType::SLIDER, settings: [], status: GalleryStatus::PUBLISHED, ordering: 0, imagesCount: 0),
        ]);

        $repository->shouldReceive('getAllWithImageCount')->once()->andReturn($galleries);

        $filters = new GalleryFiltersData(type: 'slider');
        $result = $useCase->execute($filters);

        $this->assertCount(1, $result);
        $this->assertEquals(GalleryType::SLIDER, $result->first()->type);
    }

    public function test_execute_filters_by_status(): void
    {
        $repository = Mockery::mock(GalleryRepositoryInterface::class);
        $useCase = new GetGalleryListUseCase($repository);

        $galleries = new Collection([
            new Gallery(id: 1, title: 'A', type: GalleryType::GRID, settings: [], status: GalleryStatus::PUBLISHED, ordering: 0, imagesCount: 0),
            new Gallery(id: 2, title: 'B', type: GalleryType::GRID, settings: [], status: GalleryStatus::DRAFT, ordering: 0, imagesCount: 0),
        ]);

        $repository->shouldReceive('getAllWithImageCount')->once()->andReturn($galleries);

        $filters = new GalleryFiltersData(status: true);
        $result = $useCase->execute($filters);

        $this->assertCount(1, $result);
        $this->assertTrue($result->first()->isPublished());
    }
}
