<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\GalleryManager\Infrastructure\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Modules\GalleryManager\Infrastructure\Models\GalleryModelFactory;
use Tests\Feature\Modules\GalleryManager\Infrastructure\Models\GalleryImageModelFactory;
use Tests\TestCase;

class WebGalleryControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_show_returns_published_gallery_with_images(): void
    {
        $gallery = (new GalleryModelFactory())->create(['status' => true]);
        (new GalleryImageModelFactory())->count(2)->create(['gallery_id' => $gallery->id]);

        $response = $this->getJson("/galleries/{$gallery->id}");

        $response->assertOk()
            ->assertJsonPath('id', $gallery->id)
            ->assertJsonPath('title', $gallery->title)
            ->assertJsonCount(2, 'images');
    }

    public function test_show_returns_404_for_draft_gallery(): void
    {
        $gallery = (new GalleryModelFactory())->draft()->create();

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
}
