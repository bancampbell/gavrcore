<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\FormBuilder\Infrastructure\Http\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Feature\Modules\FormBuilder\Infrastructure\Models\FormModelFactory;
use Tests\TestCase;

/**
 * Форматная валидация по ТИПУ поля на полях с произвольными именами
 * (доработка фикса email/number-правил): name='e-mail' type=email,
 * name='telefon' type=phone, name='kolichestvo' type=number. Мусор во
 * всех полях → 422 с русскими текстами и ключами по именам полей;
 * корректные значения → 200. Телефон: серверный regex, граничные кейсы.
 */
class WebFormTypedFieldsValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ThrottleRequests::class);
    }

    public function test_garbage_in_typed_fields_with_arbitrary_names_returns_422(): void
    {
        $form = (new FormModelFactory)->create([
            'fields' => [
                ['name' => 'e-mail', 'label' => 'Контактный e-mail', 'type' => 'email', 'required' => true, 'validate_email' => true],
                ['name' => 'telefon', 'label' => 'Контактный телефон', 'type' => 'phone', 'required' => true, 'validate_phone' => true],
                ['name' => 'kolichestvo', 'label' => 'Количество', 'type' => 'number', 'required' => true],
            ],
        ]);

        $response = $this->postJson("/api/forms/{$form->id}/submit", [
            'e-mail' => 'qwe',
            'telefon' => 'trutururtu',
            'kolichestvo' => 'qwe',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Ошибка валидации');

        $errors = $response->json('errors');
        $this->assertArrayHasKey('e-mail', $errors);
        $this->assertArrayHasKey('telefon', $errors);
        $this->assertArrayHasKey('kolichestvo', $errors);

        $this->assertStringContainsString('корректный email-адрес', $errors['e-mail'][0]);
        $this->assertStringContainsString('«Контактный e-mail»', $errors['e-mail'][0]);
        $this->assertStringContainsString('корректный номер телефона', $errors['telefon'][0]);
        $this->assertStringContainsString('«Контактный телефон»', $errors['telefon'][0]);
        $this->assertStringContainsString('числом', $errors['kolichestvo'][0]);
        $this->assertStringNotContainsString('The ', $errors['telefon'][0]);
    }

    public function test_valid_values_in_typed_fields_with_arbitrary_names_submit_ok(): void
    {
        $form = (new FormModelFactory)->create([
            'fields' => [
                ['name' => 'e-mail', 'label' => 'Контактный e-mail', 'type' => 'email', 'required' => true],
                ['name' => 'telefon', 'label' => 'Контактный телефон', 'type' => 'phone', 'required' => true],
                ['name' => 'kolichestvo', 'label' => 'Количество', 'type' => 'number', 'required' => true],
            ],
        ]);

        $response = $this->postJson("/api/forms/{$form->id}/submit", [
            'e-mail' => 'ivan@example.com',
            'telefon' => '+7 (900) 123-45-67',
            'kolichestvo' => 5,
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Форма успешно отправлена');
    }

    #[DataProvider('phoneProvider')]
    public function test_phone_field_accepts_russian_formats_and_rejects_letters(string $value, bool $shouldPass): void
    {
        $form = (new FormModelFactory)->create([
            'fields' => [
                ['name' => 'telefon', 'label' => 'Телефон', 'type' => 'phone', 'required' => false],
            ],
        ]);

        $response = $this->postJson("/api/forms/{$form->id}/submit", [
            'telefon' => $value,
        ]);

        if ($shouldPass) {
            $response->assertOk();
        } else {
            $response->assertStatus(422);
            $this->assertStringContainsString(
                'корректный номер телефона',
                $response->json('errors.telefon.0'),
            );
        }
    }

    public static function phoneProvider(): array
    {
        return [
            'intl with mask chars' => ['+7 (900) 123-45-67', true],
            '8xxxxxxxxxx' => ['89001234567', true],
            '+7xxxxxxxxxx' => ['+79001234567', true],
            'plain 10 digits' => ['9001234567', true],
            'letters' => ['trutururtu', false],
            'no digits, mask chars only' => ['+-() ', false],
            'too short' => ['+7', false],
        ];
    }

    public function test_url_and_datetime_fields_validate_format(): void
    {
        $form = (new FormModelFactory)->create([
            'fields' => [
                ['name' => 'ssylka', 'label' => 'Ссылка', 'type' => 'url', 'required' => false],
                ['name' => 'data', 'label' => 'Дата', 'type' => 'date', 'required' => false],
                ['name' => 'vremya', 'label' => 'Время приёма', 'type' => 'datetime', 'required' => false],
            ],
        ]);

        $response = $this->postJson("/api/forms/{$form->id}/submit", [
            'ssylka' => 'не-url',
            'data' => '21.11.2026',
            'vremya' => 'завтра утром',
        ]);

        $response->assertStatus(422);
        $errors = $response->json('errors');
        $this->assertStringContainsString('URL', $errors['ssylka'][0]);
        $this->assertStringContainsString('формат даты', $errors['data'][0]);
        $this->assertStringContainsString('формат даты', $errors['vremya'][0]);

        $this->postJson("/api/forms/{$form->id}/submit", [
            'ssylka' => 'https://example.com/page',
            'data' => '2026-11-21',
            'vremya' => '2026-11-21T09:30',
        ])->assertOk();
    }
}
