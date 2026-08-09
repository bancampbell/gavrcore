<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\MaterialManager\Infrastructure\Models;

use App\Modules\MaterialManager\Infrastructure\Models\MaterialModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class MaterialModelFactory extends Factory
{
    protected $model = MaterialModel::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'slug' => $this->faker->unique()->slug(),
            'content' => $this->faker->paragraph(),
            'category_id' => null,
            'user_id' => \App\Models\User::factory(),
            'state' => 'draft',
            'access' => 'public',
            'views' => 0,
            'published_at' => null,
            'featured' => false,
            'show_on_homepage' => false,
            'show_date' => true,
            'show_author' => true,
            'show_category' => true,
            'show_views' => true,
            'use_global_settings' => true,
            'template' => null,
            'alias' => fn (array $attributes) => $attributes['slug'],
            'meta_title' => null,
            'meta_description' => null,
            'meta_keywords' => null,
        ];
    }

    public function published(): self
    {
        return $this->state(fn (array $attributes) => [
            'state' => 'published',
            'published_at' => now(),
        ]);
    }

    public function trashed(): self
    {
        return $this->state(fn (array $attributes) => [
            'state' => 'trash',
            'deleted_at' => now(),
        ]);
    }
}
