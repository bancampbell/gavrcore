<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\GalleryManager\Infrastructure\Repositories;

use App\Modules\GalleryManager\Domain\Entities\Gallery;
use App\Modules\GalleryManager\Domain\Entities\GalleryImage;
use App\Modules\GalleryManager\Domain\ValueObjects\GalleryStatus;
use App\Modules\GalleryManager\Domain\ValueObjects\GalleryType;
use App\Modules\GalleryManager\Infrastructure\Repositories\GalleryRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\Feature\Modules\GalleryManager\Infrastructure\Models\GalleryModelFactory;
use Tests\Feature\Modules\GalleryManager\Infrastructure\Models\GalleryImageModelFactory;
use Tests\TestCase;

class GalleryRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private GalleryRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new GalleryRepository();
        Event::fake();
    }

    public function test_find_by_id_returns_gallery_entity(): void
    {
        $model = (new GalleryModelFactory())->create(['title' => 'Test']);

        $gallery = $this->repository->findById($model->id);

        $this->assertInstanceOf(Gallery::class, $gallery);
        $this->assertEquals($model->id, $gallery->id);
        $this->assertEquals('Test', $gallery->title);
    }

    public function test_find_by_id_returns_null_for_non_existing(): void
    {
        $this->assertNull($this->repository->findById(9999));
    }

    public function test_find_by_id_with_images_loads_relationship(): void
    {
        $model = (new GalleryModelFactory())->create();
        (new GalleryImageModelFactory())->count(2)->create(['gallery_id' => $model->id]);

        $gallery = $this->repository->findByIdWithImages($model->id);

        $this->assertCount(2, $gallery->images());
    }

    public function test_find_image_by_id_returns_gallery_image_entity(): void
    {
        $model = (new GalleryModelFactory())->create();
        $imageModel = (new GalleryImageModelFactory())->create(['gallery_id' => $model->id]);

        $image = $this->repository->findImageById($imageModel->id);

        $this->assertInstanceOf(GalleryImage::class, $image);
        $this->assertEquals($imageModel->id, $image->id);
    }

    public function test_get_all_returns_collection_of_entities(): void
    {
        (new GalleryModelFactory())->count(3)->create();

        $galleries = $this->repository->getAll();

        $this->assertCount(3, $galleries);
        $this->assertContainsOnlyInstancesOf(Gallery::class, $galleries);
    }

    public function test_get_all_with_image_count_includes_count(): void
    {
        $gallery = (new GalleryModelFactory())->create();
        (new GalleryImageModelFactory())->count(5)->create(['gallery_id' => $gallery->id]);

        $galleries = $this->repository->getAllWithImageCount();

        $found = $galleries->first(fn (Gallery $g) => $g->id === $gallery->id);
        $this->assertNotNull($found);
        $this->assertEquals(5, $found->imagesCount);
    }

    public function test_create_persists_gallery_and_dispatches_event(): void
    {
        $gallery = Gallery::create(
            title: 'New Gallery',
            type: GalleryType::GRID,
            settings: ['key' => 'value'],
            status: GalleryStatus::PUBLISHED,
        );

        $result = $this->repository->create($gallery);

        $this->assertDatabaseHas('galleries', ['title' => 'New Gallery']);
        $this->assertNotNull($result->id);
        Event::assertDispatched(\App\Modules\GalleryManager\Domain\Events\GalleryCreated::class);
    }

    public function test_update_persists_changes_and_dispatches_events(): void
    {
        $model = (new GalleryModelFactory())->create(['title' => 'Old']);
        $gallery = $this->repository->findById($model->id);
        $gallery->update(
            title: 'Updated',
            type: GalleryType::SLIDER,
            settings: [],
            status: GalleryStatus::DRAFT,
        );

        $result = $this->repository->update($gallery);

        $this->assertDatabaseHas('galleries', [
            'id' => $model->id,
            'title' => 'Updated',
            'type' => 'slider',
            'status' => false,
        ]);
        Event::assertDispatched(\App\Modules\GalleryManager\Domain\Events\GalleryUpdated::class);
    }

    public function test_delete_removes_gallery(): void
    {
        $model = (new GalleryModelFactory())->create();

        $this->repository->delete($model->id);

        $this->assertDatabaseMissing('galleries', ['id' => $model->id]);
    }

    public function test_add_image_creates_image_with_incremented_ordering(): void
    {
        $gallery = (new GalleryModelFactory())->create();
        (new GalleryImageModelFactory())->create([
            'gallery_id' => $gallery->id,
            'ordering' => 5,
        ]);

        $image = $this->repository->addImage($gallery->id, [
            'image_path' => '/test.jpg',
            'title' => 'Test',
            'status' => true,
        ]);

        $this->assertInstanceOf(GalleryImage::class, $image);
        $this->assertEquals(6, $image->ordering);
        $this->assertDatabaseHas('gallery_images', [
            'gallery_id' => $gallery->id,
            'ordering' => 6,
        ]);
    }

    public function test_update_image_updates_meta_fields(): void
    {
        $gallery = (new GalleryModelFactory())->create();
        $imageModel = (new GalleryImageModelFactory())->create([
            'gallery_id' => $gallery->id,
            'title' => 'Old Title',
        ]);

        $result = $this->repository->updateImage($imageModel->id, [
            'title' => 'New Title',
            'description' => 'New Desc',
            'alt_text' => 'New Alt',
            'link' => 'https://example.com',
        ]);

        $this->assertEquals('New Title', $result->title);
        $this->assertEquals('New Desc', $result->description);
        $this->assertDatabaseHas('gallery_images', [
            'id' => $imageModel->id,
            'title' => 'New Title',
        ]);
    }

    public function test_delete_image_removes_record(): void
    {
        $gallery = (new GalleryModelFactory())->create();
        $imageModel = (new GalleryImageModelFactory())->create(['gallery_id' => $gallery->id]);

        $this->repository->deleteImage($imageModel->id);

        $this->assertDatabaseMissing('gallery_images', ['id' => $imageModel->id]);
    }
}
