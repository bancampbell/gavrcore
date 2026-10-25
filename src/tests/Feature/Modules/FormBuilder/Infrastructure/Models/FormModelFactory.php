<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\FormBuilder\Infrastructure\Models;

use App\Modules\FormBuilder\Infrastructure\Models\FormModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class FormModelFactory extends Factory
{
    protected $model = FormModel::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'alias' => fake()->unique()->slug(2),
            'description' => null,
            'fields' => [
                ['name' => 'name', 'label' => 'Имя', 'type' => 'text', 'required' => true],
                ['name' => 'message', 'label' => 'Сообщение', 'type' => 'textarea', 'required' => true],
            ],
            'settings' => [],
            'notification_emails' => [],
            'status' => true,
            'is_dynamic' => false,
            'submissions_count' => 0,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => false,
        ]);
    }
}
