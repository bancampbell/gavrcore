<?php

namespace Database\Factories;

use App\Models\Material;
use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Material>
 */
class MaterialFactory extends Factory
{
    protected $model = Material::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'alias' => $this->faker->slug(),
            'content' => $this->faker->paragraphs(3, true),
            'category_id' => Category::factory(),
            'user_id' => User::factory(),
            'state' => $this->faker->randomElement(['draft', 'published', 'trash']),
            'access' => 'public',
            'views' => $this->faker->numberBetween(0, 1000),
            'published_at' => $this->faker->optional()->dateTime(),
            'featured' => $this->faker->boolean(10),
        ];
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => 'published',
            'published_at' => now(),
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => 'draft',
        ]);
    }

    public function trash(): static
    {
        return $this->state(fn (array $attributes) => [
            'state' => 'trash',
        ]);
    }
}
