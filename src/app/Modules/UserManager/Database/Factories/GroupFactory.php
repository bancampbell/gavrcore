<?php

namespace App\Modules\UserManager\Database\Factories;

use App\Modules\UserManager\Infrastructure\Models\GroupModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GroupModel>
 */
class GroupFactory extends Factory
{
    protected $model = GroupModel::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(2, true),
            'alias' => $this->faker->unique()->slug(),
            'description' => $this->faker->optional()->paragraph(),
            'status' => $this->faker->boolean(80),
            'ordering' => $this->faker->numberBetween(0, 100),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => false,
        ]);
    }
}
