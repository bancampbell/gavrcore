<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\GalleryManager\Application\UseCases;

use App\Modules\GalleryManager\Application\UseCases\UploadImageUseCase;
use App\Modules\GalleryManager\Domain\Entities\GalleryImage;
use App\Modules\GalleryManager\Domain\Repositories\GalleryRepositoryInterface;
use App\Modules\GalleryManager\Domain\Services\ImageStorageServiceInterface;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\TestCase;
use Mockery;

class UploadImageUseCaseTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_execute_stores_file_and_returns_image_array(): void
    {
        $repository = Mockery::mock(GalleryRepositoryInterface::class);
        $storage = Mockery::mock(ImageStorageServiceInterface::class);
        $useCase = new UploadImageUseCase($repository, $storage);

        $file = Mockery::mock(UploadedFile::class);
        $file->shouldReceive('getClientOriginalName')->andReturn('original.jpg');

        $storage->shouldReceive('store')
            ->once()
            ->with($file, 'galleries/5')
            ->andReturn('/storage/galleries/5/test.jpg');

        $repository->shouldReceive('addImage')
            ->once()
            ->with(5, Mockery::on(fn (array $data) =>
                $data['image_path'] === '/storage/galleries/5/test.jpg'
                && $data['title'] === 'Custom Title'
                && $data['ordering'] === 0
                && $data['status'] === true
            ))
            ->andReturn(new GalleryImage(
                id: 10, galleryId: 5, imagePath: '/storage/galleries/5/test.jpg',
                title: 'Custom Title', description: null, altText: null,
                link: null, ordering: 0, status: true,
            ));

        $result = $useCase->execute(5, $file, 'Custom Title');

        $this->assertEquals(10, $result['id']);
        $this->assertEquals('/storage/galleries/5/test.jpg', $result['image_path']);
        $this->assertEquals('Custom Title', $result['title']);
    }

    public function test_execute_uses_original_filename_when_title_null(): void
    {
        $repository = Mockery::mock(GalleryRepositoryInterface::class);
        $storage = Mockery::mock(ImageStorageServiceInterface::class);
        $useCase = new UploadImageUseCase($repository, $storage);

        $file = Mockery::mock(UploadedFile::class);
        $file->shouldReceive('getClientOriginalName')->andReturn('photo.png');

        $storage->shouldReceive('store')->andReturn('/storage/galleries/1/photo.png');

        $repository->shouldReceive('addImage')
            ->once()
            ->with(1, Mockery::on(fn (array $data) =>
                $data['title'] === 'photo.png'
            ))
            ->andReturn(new GalleryImage(
                id: 1, galleryId: 1, imagePath: '/storage/galleries/1/photo.png',
                title: 'photo.png', description: null, altText: null,
                link: null, ordering: 0, status: true,
            ));

        $result = $useCase->execute(1, $file, null);

        $this->assertEquals('photo.png', $result['title']);
    }
}
