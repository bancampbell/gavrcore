<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\GalleryManager\Application\UseCases;

use App\Modules\GalleryManager\Application\UseCases\DeleteGalleryUseCase;
use App\Modules\GalleryManager\Domain\Repositories\GalleryRepositoryInterface;
use App\Modules\GalleryManager\Domain\Services\ImageStorageServiceInterface;
use PHPUnit\Framework\TestCase;
use Mockery;

class DeleteGalleryUseCaseTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_execute_deletes_gallery_by_id(): void
    {
        $repository = Mockery::mock(GalleryRepositoryInterface::class);
        $storage = Mockery::mock(ImageStorageServiceInterface::class);
        $useCase = new DeleteGalleryUseCase($repository, $storage);

        $repository->shouldReceive('findByIdWithImages')
            ->once()
            ->with(1)
            ->andReturn(null);

        $repository->shouldReceive('delete')
            ->once()
            ->with(1);

        $useCase->execute(1);

        $this->assertTrue(true);
    }
}
