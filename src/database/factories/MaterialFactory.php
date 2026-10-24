<?php

namespace Database\Factories;

use App\Modules\CategoryManager\Infrastructure\Models\CategoryModel;
use App\Modules\MaterialManager\Infrastructure\Models\MaterialModel;
use App\Modules\UserManager\Infrastructure\Models\UserModel as User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MaterialModel>
 */
class MaterialFactory extends Factory
{
    protected $model = MaterialModel::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'slug' => $this->faker->unique()->slug(),
            'alias' => fn (array $attributes) => $attributes['slug'],
            'content' => $this->faker->paragraphs(3, true),
            'category_id' => CategoryModel::factory(),
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
