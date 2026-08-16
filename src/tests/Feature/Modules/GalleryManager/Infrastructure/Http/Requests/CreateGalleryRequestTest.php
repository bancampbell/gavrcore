<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\GalleryManager\Infrastructure\Http\Requests;

use App\Modules\GalleryManager\Infrastructure\Http\Requests\CreateGalleryRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class CreateGalleryRequestTest extends TestCase
{
    public function test_authorize_returns_true(): void
    {
        $request = new CreateGalleryRequest();
        $this->assertTrue($request->authorize());
    }

    public function test_rules_require_title_and_type(): void
    {
        $request = new CreateGalleryRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('title', $rules);
        $this->assertArrayHasKey('type', $rules);
        $this->assertContains('required', $rules['title']);
        $this->assertContains('required', $rules['type']);
    }

    public function test_rules_type_must_be_valid_enum_value(): void
    {
        $request = new CreateGalleryRequest();
        $rules = $request->rules();

        $validator = Validator::make(['type' => 'invalid'], ['type' => $rules['type']]);
        $this->assertTrue($validator->fails());

        foreach (['grid', 'slideshow', 'slider', 'switcher'] as $validType) {
            $validator = Validator::make(['type' => $validType], ['type' => $rules['type']]);
            $this->assertFalse($validator->fails(), "Type {$validType} should be valid");
        }
    }

    public function test_rules_settings_is_nullable_array(): void
    {
        $request = new CreateGalleryRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('settings', $rules);
        $this->assertContains('nullable', $rules['settings']);
        $this->assertContains('array', $rules['settings']);
    }

    public function test_rules_status_is_boolean(): void
    {
        $request = new CreateGalleryRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('status', $rules);
        $this->assertContains('boolean', $rules['status']);
    }

    public function test_prepare_for_validation_merges_default_status(): void
    {
        $request = new CreateGalleryRequest();
        $request->merge(['title' => 'Test', 'type' => 'grid']);
        $request->setContainer($this->app);

        $reflector = new \ReflectionMethod($request, 'prepareForValidation');
        $reflector->setAccessible(true);
        $reflector->invoke($request);

        $this->assertTrue($request->input('status'));
    }
}
