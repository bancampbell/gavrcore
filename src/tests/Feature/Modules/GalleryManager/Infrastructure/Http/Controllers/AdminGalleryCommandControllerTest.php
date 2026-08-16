<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\GalleryManager\Infrastructure\Http\Controllers;

use App\Models\User;
use App\Modules\GalleryManager\Domain\Services\ImageStorageServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Gate;
use Mockery;
use Tests\Feature\Modules\GalleryManager\Infrastructure\Models\GalleryModelFactory;
use Tests\Feature\Modules\GalleryManager\Infrastructure\Models\GalleryImageModelFactory;
use Tests\TestCase;

class AdminGalleryCommandControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
        Gate::before(fn (User $user) => $user->id === $this->admin->id ? true : null);
        Storage::fake('public');
    }

    /** @return array<class-string> */
    private function adminMiddleware(): array
    {
        return [\App\Http\Middleware\AdminMiddleware::class];
    }

    private function createFakeImage(string $name = 'test.jpg'): UploadedFile
    {
        $tmp = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tmp, "\xFF\xD8\xFF\xE0\x00\x10JFIF\x00\x01\x01\x01\x00\x48\x00\x48\x00\x00\xFF\xD9");
        return new UploadedFile($tmp, $name, 'image/jpeg', null, true);
    }

    public function test_store_creates_gallery_and_returns_json(): void
    {
        $response = $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->withHeader('Accept', 'application/json')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('admin.galleries.store'), [
                'title' => 'New Gallery',
                'type' => 'grid',
                'settings' => ['gutter' => 10],
                'status' => true,
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Галерея создана',
            ])
            ->assertJsonStructure(['id']);

        $this->assertDatabaseHas('galleries', [
            'title' => 'New Gallery',
            'type' => 'grid',
            'status' => true,
        ]);
    }

    public function test_store_validates_required_fields(): void
    {
        $response = $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->withHeader('Accept', 'application/json')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('admin.galleries.store'), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'type']);
    }

    public function test_store_sets_default_status_when_omitted(): void
    {
        $response = $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->withHeader('Accept', 'application/json')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('admin.galleries.store'), [
                'title' => 'Default Status',
                'type' => 'grid',
            ]);

        $response->assertOk();
        $this->assertDatabaseHas('galleries', [
            'title' => 'Default Status',
            'status' => true,
        ]);
    }

    public function test_update_updates_gallery(): void
    {
        $gallery = (new GalleryModelFactory())->create();

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->withHeader('Accept', 'application/json')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->put(route('admin.galleries.update', $gallery->id), [
                'title' => 'Updated Title',
                'type' => 'slider',
                'settings' => [],
                'status' => false,
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Галерея обновлена',
            ]);

        $this->assertDatabaseHas('galleries', [
            'id' => $gallery->id,
            'title' => 'Updated Title',
            'type' => 'slider',
            'status' => false,
        ]);
    }

    public function test_destroy_deletes_gallery(): void
    {
        $gallery = (new GalleryModelFactory())->create();

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->withHeader('Accept', 'application/json')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->delete(route('admin.galleries.destroy', $gallery->id));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Галерея удалена',
            ]);

        $this->assertDatabaseMissing('galleries', ['id' => $gallery->id]);
    }

    public function test_publish_publishes_gallery(): void
    {
        $gallery = (new GalleryModelFactory())->draft()->create();

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->withHeader('Accept', 'application/json')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('admin.galleries.publish', $gallery->id));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Галерея опубликована',
            ]);

        $this->assertDatabaseHas('galleries', [
            'id' => $gallery->id,
            'status' => true,
        ]);
    }

    public function test_unpublish_unpublishes_gallery(): void
    {
        $gallery = (new GalleryModelFactory())->create(['status' => true]);

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->withHeader('Accept', 'application/json')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('admin.galleries.unpublish', $gallery->id));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Галерея снята с публикации',
            ]);

        $this->assertDatabaseHas('galleries', [
            'id' => $gallery->id,
            'status' => false,
        ]);
    }

    public function test_upload_image_stores_file_and_returns_image_data(): void
    {
        $gallery = (new GalleryModelFactory())->create();
        $file = $this->createFakeImage('test.jpg');

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->withHeader('Accept', 'application/json')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->post(route('admin.galleries.images.upload', $gallery->id), [
                'image' => $file,
                'title' => 'Test Image',
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Изображение загружено',
            ])
            ->assertJsonStructure(['image' => ['id', 'gallery_id', 'image_path', 'title']]);

        $json = $response->json('image');
        $this->assertNotEmpty($json['image_path']);
        $this->assertStringContainsString('galleries', $json['image_path']);
    }

    public function test_update_image_updates_meta(): void
    {
        $gallery = (new GalleryModelFactory())->create();
        $image = (new GalleryImageModelFactory())->create(['gallery_id' => $gallery->id]);

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->withHeader('Accept', 'application/json')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->put(route('admin.galleries.images.update', [$gallery->id, $image->id]), [
                'title' => 'New Title',
                'description' => 'New Description',
                'alt_text' => 'Alt Text',
                'link' => 'https://example.com',
            ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Изображение обновлено',
            ]);

        $this->assertDatabaseHas('gallery_images', [
            'id' => $image->id,
            'title' => 'New Title',
            'description' => 'New Description',
            'alt_text' => 'Alt Text',
            'link' => 'https://example.com',
        ]);
    }

    public function test_delete_image_removes_image_record(): void
    {
        $gallery = (new GalleryModelFactory())->create();
        $image = (new GalleryImageModelFactory())->create([
            'gallery_id' => $gallery->id,
            'image_path' => '/storage/galleries/test.jpg',
        ]);

        $mockStorage = Mockery::mock(ImageStorageServiceInterface::class);
        $mockStorage->shouldReceive('delete')
            ->once()
            ->with('/storage/galleries/test.jpg');

        $this->app->instance(ImageStorageServiceInterface::class, $mockStorage);

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware($this->adminMiddleware())
            ->withHeader('Accept', 'application/json')
            ->withHeader('X-Requested-With', 'XMLHttpRequest')
            ->delete(route('admin.galleries.images.delete', [$gallery->id, $image->id]));

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Изображение удалено',
            ]);

        $this->assertDatabaseMissing('gallery_images', ['id' => $image->id]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
