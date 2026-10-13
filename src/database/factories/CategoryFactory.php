<?php

namespace Database\Factories;

use App\Modules\CategoryManager\Infrastructure\Models\CategoryModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CategoryModel>
 */
class CategoryFactory extends Factory
{
    protected $model = CategoryModel::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(2, true),
            'alias' => $this->faker->unique()->slug(),
            'description' => $this->faker->optional()->paragraph(),
            'parent_id' => null,
            'lft' => 0,
            'rgt' => 0,
            'depth' => 0,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function withParent(CategoryModel $parent): static
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent->id,
            'depth' => $parent->depth + 1,
        ]);
    }
}
