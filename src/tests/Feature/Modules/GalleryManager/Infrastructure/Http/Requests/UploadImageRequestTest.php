<?php

declare(strict_types=1);

namespace Tests\Feature\Modules\GalleryManager\Infrastructure\Http\Requests;

use App\Modules\GalleryManager\Infrastructure\Http\Requests\UploadImageRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class UploadImageRequestTest extends TestCase
{
    public function test_authorize_returns_true(): void
    {
        $request = new UploadImageRequest();
        $this->assertTrue($request->authorize());
    }

    public function test_rules_require_image(): void
    {
        $request = new UploadImageRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('image', $rules);
        $this->assertContains('required', $rules['image']);
        $this->assertContains('image', $rules['image']);
    }

    public function test_rules_title_is_optional_string(): void
    {
        $request = new UploadImageRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('title', $rules);
        $this->assertContains('nullable', $rules['title']);
        $this->assertContains('string', $rules['title']);
    }

    public function test_image_must_be_valid_image_file(): void
    {
        $request = new UploadImageRequest();
        $rules = $request->rules();

        $tmp = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tmp, "\xFF\xD8\xFF\xE0\x00\x10JFIF\x00\x01\x01\x01\x00\x48\x00\x48\x00\x00\xFF\xD9");
        $image = new UploadedFile($tmp, 'photo.jpg', 'image/jpeg', null, true);

        $validator = Validator::make(['image' => $image], ['image' => $rules['image']]);
        $this->assertFalse($validator->fails());

        $textFile = UploadedFile::fake()->create('document.txt', 100);
        $validator = Validator::make(['image' => $textFile], ['image' => $rules['image']]);
        $this->assertTrue($validator->fails());
    }
}
