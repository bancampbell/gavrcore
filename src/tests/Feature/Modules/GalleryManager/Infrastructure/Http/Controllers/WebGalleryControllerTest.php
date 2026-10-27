<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\GalleryManager\Infrastructure\Http\Controllers;

use App\Modules\GalleryManager\Database\Seeders\GallerySeeder;
use App\Modules\GalleryManager\Infrastructure\Models\GalleryImageModel;
use App\Modules\GalleryManager\Infrastructure\Models\GalleryModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Modules\GalleryManager\Infrastructure\Models\GalleryImageModelFactory;
use Tests\Feature\Modules\GalleryManager\Infrastructure\Models\GalleryModelFactory;
use Tests\TestCase;

class WebGalleryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_returns_published_gallery_with_images(): void
    {
        $gallery = (new GalleryModelFactory)->create(['status' => true]);
        (new GalleryImageModelFactory)->count(2)->create(['gallery_id' => $gallery->id]);

        $response = $this->getJson("/galleries/{$gallery->id}");

        $response->assertOk()
            ->assertJsonPath('id', $gallery->id)
            ->assertJsonPath('title', $gallery->title)
            ->assertJsonCount(2, 'images');
    }

    public function test_show_returns_404_for_draft_gallery(): void
    {
        $gallery = (new GalleryModelFactory)->draft()->create();

        $response = $this->getJson("/galleries/{$gallery->id}");

        $response->assertNotFound()
            ->assertJson(['message' => 'Gallery not found or not published']);
    }

    public function test_show_returns_404_for_non_existing_gallery(): void
    {
        $response = $this->getJson('/galleries/9999');

        $response->assertNotFound()
            ->assertJson(['message' => 'Gallery not found or not published']);
    }

    public function test_seeded_galleries_are_publicly_available(): void
    {
        // AUDIT-20: GallerySeeder создаёт демо-галереи с placeholder-файлами
        $this->seed(GallerySeeder::class);

        $galleries = GalleryModel::with('images')->get();

        $this->assertCount(3, $galleries);

        foreach ($galleries as $gallery) {
            $this->assertTrue($gallery->status);
            $this->assertCount(3, $gallery->images);

            foreach ($gallery->images as $image) {
                Storage::disk('public')->assertExists($image->image_path);
            }

            $this->getJson("/galleries/{$gallery->id}")
                ->assertOk()
                ->assertJsonPath('id', $gallery->id)
                ->assertJsonPath('title', $gallery->title)
                ->assertJsonCount(3, 'images');
        }
    }

    public function test_seeder_is_idempotent(): void
    {
        $this->seed(GallerySeeder::class);
        $this->seed(GallerySeeder::class);

        $this->assertSame(3, GalleryModel::count());
        $this->assertSame(9, GalleryImageModel::count());
    }
}
