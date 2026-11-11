<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\FormBuilder\Infrastructure\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Modules\FormBuilder\Infrastructure\Models\FormModelFactory;
use Tests\TestCase;

/**
 * Throttle публичного submit формы: 5 попыток/минуту с IP, 6-я подряд — 429.
 */
class FormSubmitThrottleTest extends TestCase
{
    use RefreshDatabase;

    public function test_sixth_consecutive_submit_is_rejected_with_429(): void
    {
        $form = (new FormModelFactory)->create();

        for ($i = 0; $i < 5; $i++) {
            $this->postJson("/api/forms/{$form->id}/submit", [
                'name' => 'Пользователь '.$i,
                'message' => 'Сообщение '.$i,
            ])->assertOk();
        }

        $response = $this->postJson("/api/forms/{$form->id}/submit", [
            'name' => 'Бот',
            'message' => 'Шестая попытка подряд',
        ]);

        $response->assertStatus(429);

        $message = (string) $response->json('message');
        $this->assertStringContainsString('Слишком много попыток', $message);
    }
}
