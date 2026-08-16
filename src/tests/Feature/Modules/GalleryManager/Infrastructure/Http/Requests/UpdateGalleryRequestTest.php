<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\GalleryManager\Infrastructure\Http\Requests;

use App\Modules\GalleryManager\Infrastructure\Http\Requests\UpdateGalleryRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UpdateGalleryRequestTest extends TestCase
{
    public function test_authorize_returns_true(): void
    {
        $request = new UpdateGalleryRequest();
        $this->assertTrue($request->authorize());
    }

    public function test_rules_structure(): void
    {
        $request = new UpdateGalleryRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('title', $rules);
        $this->assertArrayHasKey('type', $rules);
        $this->assertArrayHasKey('settings', $rules);
        $this->assertArrayHasKey('status', $rules);
        $this->assertContains('required', $rules['title']);
        $this->assertContains('nullable', $rules['settings']);
        $this->assertContains('boolean', $rules['status']);
    }

    public function test_type_must_be_valid_gallery_type(): void
    {
        $request = new UpdateGalleryRequest();
        $rules = $request->rules();

        $validator = Validator::make(['type' => 'slideshow'], ['type' => $rules['type']]);
        $this->assertFalse($validator->fails());

        $validator = Validator::make(['type' => 'invalid'], ['type' => $rules['type']]);
        $this->assertTrue($validator->fails());
    }
}
