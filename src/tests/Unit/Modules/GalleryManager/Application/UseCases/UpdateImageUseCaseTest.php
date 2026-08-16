<?php

declare(strict_types=1);

namespace Tests\Unit\Modules\GalleryManager\Application\UseCases;

use App\Modules\GalleryManager\Application\UseCases\UpdateImageUseCase;
use App\Modules\GalleryManager\Domain\Entities\GalleryImage;
use App\Modules\GalleryManager\Domain\Repositories\GalleryRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Mockery;

class UpdateImageUseCaseTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_execute_updates_image_and_returns_array(): void
    {
        $repository = Mockery::mock(GalleryRepositoryInterface::class);
        $useCase = new UpdateImageUseCase($repository);

        $image = new GalleryImage(
            id: 5, galleryId: 1, imagePath: '/test.jpg',
            title: 'New Title', description: 'New Desc',
            altText: 'Alt', link: 'https://example.com',
            ordering: 2, status: true,
        );

        $repository->shouldReceive('findImageById')
            ->with(5)
            ->once()
            ->andReturn($image);

        $repository->shouldReceive('updateImage')
            ->once()
            ->with(5, ['title' => 'New Title', 'description' => 'New Desc'])
            ->andReturn($image);

        $result = $useCase->execute(1, 5, ['title' => 'New Title', 'description' => 'New Desc']);

        $this->assertEquals(5, $result['id']);
        $this->assertEquals('New Title', $result['title']);
        $this->assertEquals('New Desc', $result['description']);
    }
}
