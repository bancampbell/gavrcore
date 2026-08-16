<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\GalleryManager\Application\UseCases;

use App\Modules\GalleryManager\Application\UseCases\DeleteImageUseCase;
use App\Modules\GalleryManager\Domain\Entities\GalleryImage;
use App\Modules\GalleryManager\Domain\Repositories\GalleryRepositoryInterface;
use App\Modules\GalleryManager\Domain\Services\ImageStorageServiceInterface;
use PHPUnit\Framework\TestCase;
use Mockery;

class DeleteImageUseCaseTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_execute_deletes_file_and_removes_record(): void
    {
        $repository = Mockery::mock(GalleryRepositoryInterface::class);
        $storage = Mockery::mock(ImageStorageServiceInterface::class);
        $useCase = new DeleteImageUseCase($repository, $storage);

        $image = new GalleryImage(
            id: 10, galleryId: 1, imagePath: '/storage/galleries/1/old.jpg',
            title: null, description: null, altText: null,
            link: null, ordering: 0, status: true,
        );

        $repository->shouldReceive('findImageById')
            ->with(10)
            ->once()
            ->andReturn($image);

        $storage->shouldReceive('delete')
            ->once()
            ->with('/storage/galleries/1/old.jpg');

        $repository->shouldReceive('deleteImage')
            ->once()
            ->with(10);

        $useCase->execute(1, 10);

        $this->assertTrue(true);
    }

    public function test_execute_throws_when_image_not_found(): void
    {
        $repository = Mockery::mock(GalleryRepositoryInterface::class);
        $storage = Mockery::mock(ImageStorageServiceInterface::class);
        $useCase = new DeleteImageUseCase($repository, $storage);

        $repository->shouldReceive('findImageById')
            ->with(999)
            ->once()
            ->andReturn(null);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Image not found');

        $useCase->execute(1, 999);
    }
}
