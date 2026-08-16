<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\GalleryManager\Infrastructure\Models;

use App\Modules\GalleryManager\Infrastructure\Models\GalleryImageModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class GalleryImageModelFactory extends Factory
{
    protected $model = GalleryImageModel::class;

    public function definition(): array
    {
        return [
            'gallery_id' => 1,
            'image_path' => '/storage/galleries/' . fake()->uuid() . '.jpg',
            'title' => fake()->optional()->words(2, true),
            'description' => fake()->optional()->sentence(),
            'alt_text' => fake()->optional()->word(),
            'link' => fake()->optional()->url(),
            'ordering' => fake()->numberBetween(0, 100),
            'status' => true,
        ];
    }
}
