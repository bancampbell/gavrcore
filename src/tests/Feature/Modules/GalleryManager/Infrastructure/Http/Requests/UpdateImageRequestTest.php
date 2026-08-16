<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\GalleryManager\Infrastructure\Http\Requests;

use App\Modules\GalleryManager\Infrastructure\Http\Requests\UpdateImageRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateImageRequestTest extends TestCase
{
    public function test_authorize_returns_true(): void
    {
        $request = new UpdateImageRequest();
        $this->assertTrue($request->authorize());
    }

    public function test_rules_allow_optional_meta_fields(): void
    {
        $request = new UpdateImageRequest();
        $rules = $request->rules();

        foreach (['title', 'description', 'alt_text', 'link'] as $field) {
            $this->assertArrayHasKey($field, $rules);
            $this->assertContains('nullable', $rules[$field]);
        }
    }

    public function test_link_must_be_valid_url_when_provided(): void
    {
        $request = new UpdateImageRequest();
        $rules = $request->rules();

        $validator = Validator::make(['link' => 'not-a-url'], ['link' => $rules['link']]);
        $this->assertTrue($validator->fails());

        $validator = Validator::make(['link' => 'https://example.com'], ['link' => $rules['link']]);
        $this->assertFalse($validator->fails());
    }
}
