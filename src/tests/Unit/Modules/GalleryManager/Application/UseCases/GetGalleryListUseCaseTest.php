<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\GalleryManager\Application\UseCases;

use App\Modules\GalleryManager\Application\DTO\GalleryFiltersData;
use App\Modules\GalleryManager\Application\UseCases\GetGalleryListUseCase;
use App\Modules\GalleryManager\Domain\Repositories\GalleryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Mockery;
use PHPUnit\Framework\TestCase;

class GetGalleryListUseCaseTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_execute_paginates_with_given_filters(): void
    {
        $repository = Mockery::mock(GalleryRepositoryInterface::class);
        $useCase = new GetGalleryListUseCase($repository);
        $paginator = Mockery::mock(LengthAwarePaginator::class);

        $filters = new GalleryFiltersData(search: 'Alpha', type: 'grid', status: true, perPage: 25, page: 2);

        $repository->shouldReceive('paginate')
            ->once()
            ->with([
                'search' => 'Alpha',
                'type' => 'grid',
                'status' => true,
                'per_page' => 25,
                'page' => 2,
            ], 25, 2)
            ->andReturn($paginator);

        $result = $useCase->execute($filters);

        $this->assertSame($paginator, $result);
    }

    public function test_execute_uses_default_filters_when_null(): void
    {
        $repository = Mockery::mock(GalleryRepositoryInterface::class);
        $useCase = new GetGalleryListUseCase($repository);
        $paginator = Mockery::mock(LengthAwarePaginator::class);

        $repository->shouldReceive('paginate')
            ->once()
            ->with([
                'search' => null,
                'type' => null,
                'status' => null,
                'per_page' => 10,
                'page' => 1,
            ], 10, 1)
            ->andReturn($paginator);

        $result = $useCase->execute();

        $this->assertSame($paginator, $result);
    }
}
