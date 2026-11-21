<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\FormBuilder\Infrastructure\Http\Controllers;

use App\Modules\FormBuilder\Infrastructure\Models\FormSubmissionModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Tests\Feature\Modules\FormBuilder\Infrastructure\Models\FormModelFactory;
use Tests\TestCase;

/**
 * Условно скрытые поля в публичном submit: required-правило не должно
 * применяться к полю, скрытому условиями показа — иначе любая форма с
 * условиями падает с 422 на полях, которые пользователь не заполнял,
 * потому что поле было скрыто (зеркало checkFieldVisibility на фронте).
 */
class WebFormConditionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ThrottleRequests::class);
    }

    private function createFormWithConditionalField(): mixed
    {
        return (new FormModelFactory)->create([
            'fields' => [
                [
                    'name' => 'category',
                    'label' => 'Категория',
                    'type' => 'radio',
                    'required' => true,
                ],
                [
                    'name' => 'details',
                    'label' => 'Детали',
                    'type' => 'textarea',
                    'required' => true,
                    'conditions' => [
                        ['field' => 'category', 'operator' => 'equals', 'value' => 'consult'],
                    ],
                ],
                [
                    'name' => 'comment',
                    'label' => 'Комментарий',
                    'type' => 'textarea',
                    'required' => false,
                    'conditions' => [
                        ['field' => 'category', 'operator' => 'not_equals', 'value' => 'consult'],
                    ],
                ],
            ],
        ]);
    }

    public function test_hidden_conditionally_required_field_does_not_fail_submit(): void
    {
        $form = $this->createFormWithConditionalField();

        // category=reception скрывает required-поле details.
        $response = $this->postJson("/api/forms/{$form->id}/submit", [
            'category' => 'reception',
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Форма успешно отправлена');

        $this->assertDatabaseHas('form_submissions', ['form_id' => $form->id]);
    }

    public function test_visible_conditionally_required_field_still_validates(): void
    {
        $form = $this->createFormWithConditionalField();

        // category=consult показывает required-поле details, но оно пустое.
        $response = $this->postJson("/api/forms/{$form->id}/submit", [
            'category' => 'consult',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Ошибка валидации')
            ->assertJsonStructure(['errors' => ['details']]);

        $this->assertDatabaseMissing('form_submissions', ['form_id' => $form->id]);
    }

    public function test_visible_conditionally_required_field_filled_submits_ok(): void
    {
        $form = $this->createFormWithConditionalField();

        $response = $this->postJson("/api/forms/{$form->id}/submit", [
            'category' => 'consult',
            'details' => 'Нужна консультация',
        ]);

        $response->assertOk();

        $submission = FormSubmissionModel::where('form_id', $form->id)->firstOrFail();
        $this->assertSame('consult', $submission->data['category']);
        $this->assertSame('Нужна консультация', $submission->data['details']);
    }
}
