<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\FormBuilder\Infrastructure\Models;

use App\Modules\FormBuilder\Domain\Entities\FormSubmission;
use App\Modules\FormBuilder\Infrastructure\Models\FormSubmissionModel;
use Illuminate\Database\Eloquent\Factories\Factory;

class FormSubmissionModelFactory extends Factory
{
    protected $model = FormSubmissionModel::class;

    public function definition(): array
    {
        return [
            'form_id' => FormModelFactory::new(),
            'user_id' => null,
            'data' => [
                'name' => fake()->name(),
                'message' => fake()->sentence(),
            ],
            'status' => FormSubmission::STATUS_NEW,
            'meta' => null,
            'read_at' => null,
        ];
    }

    public function forUser(int $userId): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $userId,
        ]);
    }

    public function withStatus(string $status): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => $status,
        ]);
    }

    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => now(),
        ]);
    }
}
