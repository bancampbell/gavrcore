<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\GalleryManager\Infrastructure\Models;

use App\Modules\GalleryManager\Infrastructure\Models\GalleryModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class GalleryModelFactory extends Factory
{
    protected $model = GalleryModel::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'type' => 'grid',
            'settings' => [],
            'status' => true,
            'ordering' => 0,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => false,
        ]);
    }

    public function type(string $type): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type,
        ]);
    }
}
