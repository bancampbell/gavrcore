<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\GalleryManager\Application\UseCases;

use App\Modules\GalleryManager\Application\UseCases\GetGalleryForEditUseCase;
use App\Modules\GalleryManager\Domain\Entities\Gallery;
use App\Modules\GalleryManager\Domain\Repositories\GalleryRepositoryInterface;
use App\Modules\GalleryManager\Domain\ValueObjects\GalleryStatus;
use App\Modules\GalleryManager\Domain\ValueObjects\GalleryType;
use PHPUnit\Framework\TestCase;
use Mockery;

class GetGalleryForEditUseCaseTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_execute_returns_gallery_array_when_found(): void
    {
        $repository = Mockery::mock(GalleryRepositoryInterface::class);
        $useCase = new GetGalleryForEditUseCase($repository);

        $gallery = new Gallery(
            id: 1, title: 'Test', type: GalleryType::GRID,
            settings: [], status: GalleryStatus::PUBLISHED, ordering: 0,
        );

        $repository->shouldReceive('findByIdWithImages')
            ->with(1)
            ->once()
            ->andReturn($gallery);

        $result = $useCase->execute(1);

        $this->assertIsArray($result);
        $this->assertEquals(1, $result['id']);
        $this->assertEquals('Test', $result['title']);
    }

    public function test_execute_returns_null_when_not_found(): void
    {
        $repository = Mockery::mock(GalleryRepositoryInterface::class);
        $useCase = new GetGalleryForEditUseCase($repository);

        $repository->shouldReceive('findByIdWithImages')
            ->with(999)
            ->once()
            ->andReturn(null);

        $result = $useCase->execute(999);

        $this->assertNull($result);
    }
}
