<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\GalleryManager\Application\UseCases;

use App\Modules\GalleryManager\Application\UseCases\GetGalleryForPublicUseCase;
use App\Modules\GalleryManager\Domain\Entities\Gallery;
use App\Modules\GalleryManager\Domain\Repositories\GalleryRepositoryInterface;
use App\Modules\GalleryManager\Domain\ValueObjects\GalleryStatus;
use App\Modules\GalleryManager\Domain\ValueObjects\GalleryType;
use PHPUnit\Framework\TestCase;
use Mockery;

class GetGalleryForPublicUseCaseTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_execute_returns_published_gallery(): void
    {
        $repository = Mockery::mock(GalleryRepositoryInterface::class);
        $useCase = new GetGalleryForPublicUseCase($repository);

        $gallery = new Gallery(
            id: 1, title: 'Public', type: GalleryType::GRID,
            settings: [], status: GalleryStatus::PUBLISHED, ordering: 0,
        );

        $repository->shouldReceive('findByIdWithImages')
            ->with(1)
            ->once()
            ->andReturn($gallery);

        $result = $useCase->execute(1);

        $this->assertIsArray($result);
        $this->assertEquals(1, $result['id']);
    }

    public function test_execute_returns_null_for_draft_gallery(): void
    {
        $repository = Mockery::mock(GalleryRepositoryInterface::class);
        $useCase = new GetGalleryForPublicUseCase($repository);

        $gallery = new Gallery(
            id: 1, title: 'Draft', type: GalleryType::GRID,
            settings: [], status: GalleryStatus::DRAFT, ordering: 0,
        );

        $repository->shouldReceive('findByIdWithImages')
            ->with(1)
            ->once()
            ->andReturn($gallery);

        $result = $useCase->execute(1);

        $this->assertNull($result);
    }

    public function test_execute_returns_null_when_not_found(): void
    {
        $repository = Mockery::mock(GalleryRepositoryInterface::class);
        $useCase = new GetGalleryForPublicUseCase($repository);

        $repository->shouldReceive('findByIdWithImages')
            ->with(999)
            ->once()
            ->andReturn(null);

        $result = $useCase->execute(999);

        $this->assertNull($result);
    }
}
