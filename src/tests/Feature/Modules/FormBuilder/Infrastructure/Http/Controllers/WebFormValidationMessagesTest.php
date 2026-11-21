<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\FormBuilder\Infrastructure\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Tests\Feature\Modules\FormBuilder\Infrastructure\Models\FormModelFactory;
use Tests\TestCase;

/**
 * Русские сообщения валидации публичного submit: тексты ошибок называют поле
 * по метке из конфигурации формы (fallback — техническое имя), а не дефолтные
 * английские сообщения Laravel; email/number-поля валидируются по типу.
 */
class WebFormValidationMessagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ThrottleRequests::class);
    }

    public function test_required_file_field_empty_returns_russian_message_with_label(): void
    {
        $form = (new FormModelFactory)->create([
            'fields' => [
                ['name' => 'attachment', 'label' => 'Прикреплённый файл', 'type' => 'file', 'required' => true],
            ],
        ]);

        $response = $this->postJson("/api/forms/{$form->id}/submit", []);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Ошибка валидации');

        $message = $response->json('errors.attachment.0');
        $this->assertIsString($message);
        $this->assertStringContainsString('«Прикреплённый файл»', $message);
        $this->assertStringContainsString('обязательно для заполнения', $message);
        $this->assertStringNotContainsString('The ', $message);
        $this->assertStringNotContainsString('field is required', $message);
    }

    public function test_field_without_label_falls_back_to_name_in_message(): void
    {
        $form = (new FormModelFactory)->create([
            'fields' => [
                ['name' => 'nickname', 'type' => 'text', 'required' => true],
            ],
        ]);

        $response = $this->postJson("/api/forms/{$form->id}/submit", []);

        $message = $response->json('errors.nickname.0');
        $this->assertIsString($message);
        $this->assertStringContainsString('«nickname»', $message);
        $this->assertStringNotContainsString('The ', $message);
    }

    public function test_email_field_rejects_garbage_with_russian_message(): void
    {
        $form = (new FormModelFactory)->create([
            'fields' => [
                ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
            ],
        ]);

        $response = $this->postJson("/api/forms/{$form->id}/submit", [
            'email' => 'выа',
        ]);

        $response->assertStatus(422);

        $message = $response->json('errors.email.0');
        $this->assertIsString($message);
        $this->assertStringContainsString('«Email»', $message);
        $this->assertStringContainsString('корректный email-адрес', $message);
        $this->assertStringNotContainsString('The ', $message);
    }

    public function test_valid_email_and_required_fields_submit_ok(): void
    {
        $form = (new FormModelFactory)->create([
            'fields' => [
                ['name' => 'name', 'label' => 'Имя', 'type' => 'text', 'required' => true],
                ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'required' => true],
            ],
        ]);

        $response = $this->postJson("/api/forms/{$form->id}/submit", [
            'name' => 'Иван',
            'email' => 'ivan@example.com',
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Форма успешно отправлена');
    }

    public function test_number_field_rejects_non_numeric_value(): void
    {
        $form = (new FormModelFactory)->create([
            'fields' => [
                ['name' => 'age', 'label' => 'Возраст', 'type' => 'number', 'required' => false, 'min_value' => 18, 'max_value' => 99],
            ],
        ]);

        $response = $this->postJson("/api/forms/{$form->id}/submit", [
            'age' => 'abc',
        ]);

        $response->assertStatus(422);

        $message = $response->json('errors.age.0');
        $this->assertIsString($message);
        $this->assertStringContainsString('числом', $message);
        $this->assertStringNotContainsString('The ', $message);
    }
}
