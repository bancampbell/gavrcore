<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\GalleryManager\Infrastructure\Repositories;

use App\Modules\GalleryManager\Domain\Entities\Gallery;
use App\Modules\GalleryManager\Domain\Entities\GalleryImage;
use App\Modules\GalleryManager\Domain\Events\GalleryCreated;
use App\Modules\GalleryManager\Domain\Events\GalleryUpdated;
use App\Modules\GalleryManager\Domain\ValueObjects\GalleryStatus;
use App\Modules\GalleryManager\Domain\ValueObjects\GalleryType;
use App\Modules\GalleryManager\Infrastructure\Repositories\GalleryRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\Feature\Modules\GalleryManager\Infrastructure\Models\GalleryImageModelFactory;
use Tests\Feature\Modules\GalleryManager\Infrastructure\Models\GalleryModelFactory;
use Tests\TestCase;

class GalleryRepositoryTest extends TestCase
{
    use RefreshDatabase;

    private GalleryRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new GalleryRepository;
        Event::fake();
    }

    public function test_find_by_id_returns_gallery_entity(): void
    {
        $model = (new GalleryModelFactory)->create(['title' => 'Test']);

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
        $model = (new GalleryModelFactory)->create();
        (new GalleryImageModelFactory)->count(2)->create(['gallery_id' => $model->id]);

        $gallery = $this->repository->findByIdWithImages($model->id);

        $this->assertCount(2, $gallery->images());
    }

    public function test_find_image_by_id_returns_gallery_image_entity(): void
    {
        $model = (new GalleryModelFactory)->create();
        $imageModel = (new GalleryImageModelFactory)->create(['gallery_id' => $model->id]);

        $image = $this->repository->findImageById($imageModel->id);

        $this->assertInstanceOf(GalleryImage::class, $image);
        $this->assertEquals($imageModel->id, $image->id);
    }

    public function test_get_all_returns_collection_of_entities(): void
    {
        (new GalleryModelFactory)->count(3)->create();

        $galleries = $this->repository->getAll();

        $this->assertCount(3, $galleries);
        $this->assertContainsOnlyInstancesOf(Gallery::class, $galleries);
    }

    public function test_paginate_returns_paginator_with_entities_and_image_count(): void
    {
        $gallery = (new GalleryModelFactory)->create();
        (new GalleryImageModelFactory)->count(5)->create(['gallery_id' => $gallery->id]);

        $result = $this->repository->paginate([]);

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals(1, $result->total());
        $item = $result->items()[0];
        $this->assertInstanceOf(Gallery::class, $item);
        $this->assertEquals(5, $item->imagesCount);
    }

    public function test_paginate_filters_by_type(): void
    {
        (new GalleryModelFactory)->create(['type' => 'grid']);
        $slider = (new GalleryModelFactory)->create(['type' => 'slider']);

        $result = $this->repository->paginate(['type' => 'slider']);

        $this->assertEquals(1, $result->total());
        $this->assertEquals($slider->id, $result->items()[0]->id);
        $this->assertEquals(GalleryType::SLIDER, $result->items()[0]->type);
    }

    public function test_paginate_filters_by_status(): void
    {
        $published = (new GalleryModelFactory)->create(['status' => true]);
        $draft = (new GalleryModelFactory)->create(['status' => false]);

        $publishedResult = $this->repository->paginate(['status' => true]);
        $this->assertEquals(1, $publishedResult->total());
        $this->assertEquals($published->id, $publishedResult->items()[0]->id);
        $this->assertTrue($publishedResult->items()[0]->isPublished());

        $draftResult = $this->repository->paginate(['status' => false]);
        $this->assertEquals(1, $draftResult->total());
        $this->assertEquals($draft->id, $draftResult->items()[0]->id);
        $this->assertFalse($draftResult->items()[0]->isPublished());
    }

    public function test_paginate_filters_by_search_substring_of_title(): void
    {
        (new GalleryModelFactory)->create(['title' => 'Alpha Gallery']);
        (new GalleryModelFactory)->create(['title' => 'Beta Gallery']);

        $result = $this->repository->paginate(['search' => 'Alpha']);

        $this->assertEquals(1, $result->total());
        $this->assertEquals('Alpha Gallery', $result->items()[0]->title);

        $lowerResult = $this->repository->paginate(['search' => 'alpha']);
        $this->assertEquals(1, $lowerResult->total());
    }

    public function test_paginate_splits_into_pages(): void
    {
        $models = collect(range(1, 25))->map(fn (int $i) => (new GalleryModelFactory)->create([
            'title' => 'Gallery '.$i,
            'created_at' => now()->subMinutes(25 - $i),
            'updated_at' => now()->subMinutes(25 - $i),
        ]));

        $firstPage = $this->repository->paginate([], 10);
        $this->assertEquals(25, $firstPage->total());
        $this->assertEquals(3, $firstPage->lastPage());
        $this->assertEquals(1, $firstPage->currentPage());
        $this->assertCount(10, $firstPage->items());

        // created_at desc — ожидаемый порядок id выводим из созданных моделей
        $expectedIds = $models
            ->sortByDesc(fn ($model) => $model->created_at->toDateTimeString())
            ->pluck('id')
            ->values();

        $secondPage = $this->repository->paginate([], 10, 2);
        $this->assertEquals(2, $secondPage->currentPage());
        $this->assertEquals(11, $secondPage->firstItem());
        $this->assertEquals(20, $secondPage->lastItem());
        $this->assertEquals(
            $expectedIds->slice(10, 10)->values()->all(),
            collect($secondPage->items())->map(fn (Gallery $g) => $g->id)->values()->all()
        );

        $thirdPage = $this->repository->paginate([], 10, 3);
        $this->assertEquals(
            $expectedIds->slice(20)->values()->all(),
            collect($thirdPage->items())->map(fn (Gallery $g) => $g->id)->values()->all()
        );
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
        Event::assertDispatched(GalleryCreated::class);
    }

    public function test_update_persists_changes_and_dispatches_events(): void
    {
        $model = (new GalleryModelFactory)->create(['title' => 'Old']);
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
        Event::assertDispatched(GalleryUpdated::class);
    }

    public function test_delete_removes_gallery(): void
    {
        $model = (new GalleryModelFactory)->create();

        $this->repository->delete($model->id);

        $this->assertDatabaseMissing('galleries', ['id' => $model->id]);
    }

    public function test_add_image_creates_image_with_incremented_ordering(): void
    {
        $gallery = (new GalleryModelFactory)->create();
        (new GalleryImageModelFactory)->create([
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
        $gallery = (new GalleryModelFactory)->create();
        $imageModel = (new GalleryImageModelFactory)->create([
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
        $gallery = (new GalleryModelFactory)->create();
        $imageModel = (new GalleryImageModelFactory)->create(['gallery_id' => $gallery->id]);

        $this->repository->deleteImage($imageModel->id);

        $this->assertDatabaseMissing('gallery_images', ['id' => $imageModel->id]);
    }
}
