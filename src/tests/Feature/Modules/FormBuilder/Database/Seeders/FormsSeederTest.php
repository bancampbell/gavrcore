<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\FormBuilder\Database\Seeders;

use App\Modules\FormBuilder\Database\Seeders\FormsSeeder;
use App\Modules\FormBuilder\Infrastructure\Models\FormModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FormsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeded_forms_use_only_registered_field_types(): void
    {
        $this->seed(FormsSeeder::class);

        $forms = FormModel::query()->pluck('fields', 'alias');

        $this->assertArrayHasKey('feedback', $forms);
        $this->assertArrayHasKey('support-request', $forms);

        // Зеркало реестра resources/js/modules/FormBuilder/ui/constants/fieldTypes.ts:
        // сидер обязан сеять только типы, которые знают фронт-реестр и FormRenderer.
        $registeredTypes = [
            'text', 'textarea', 'email', 'phone', 'number', 'date', 'select',
            'checkbox', 'radio', 'file', 'url', 'color', 'time', 'datetime',
            'range', 'rating', 'toggle',
        ];

        foreach ($forms as $alias => $fields) {
            foreach ($fields as $field) {
                $this->assertContains(
                    $field['type'],
                    $registeredTypes,
                    "Форма '{$alias}': поле '{$field['name']}' сеется с незарегистрированным type '{$field['type']}'"
                );
            }
        }
    }

    public function test_support_request_form_seeds_phone_field_with_registered_type(): void
    {
        $this->seed(FormsSeeder::class);

        $fields = FormModel::query()->where('alias', 'support-request')->value('fields');

        $phone = collect($fields)->firstWhere('name', 'phone');

        $this->assertNotNull($phone, 'Демо-форма support-request не содержит поля phone');
        $this->assertSame('phone', $phone['type']);
    }
}
