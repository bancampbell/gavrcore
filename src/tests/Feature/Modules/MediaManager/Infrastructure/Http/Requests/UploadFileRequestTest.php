<?php

namespace Tests\Unit\Modules\MediaManager\Infrastructure\Http\Requests;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Modules\MediaManager\Infrastructure\Http\Requests\UploadFileRequest;
use Tests\TestCase;

class UploadFileRequestTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Gate::define('manage-media', function () {
            return true;
        });
    }

    public function test_valid_upload_passes(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('photo.jpg', 100, 'image/jpeg');

        $request = new UploadFileRequest();
        $validator = Validator::make(
            ['files' => [$file]],
            $request->rules()
        );

        $this->assertFalse($validator->fails());
    }

    public function test_exe_file_fails_mimetypes(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('virus.exe', 100);

        $request = new UploadFileRequest();
        $validator = Validator::make(
            ['files' => [$file]],
            $request->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('files.0', $validator->errors()->toArray());
    }

    public function test_renamed_exe_fails_mimetypes(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('virus.jpg', 100, 'application/x-msdownload');

        $request = new UploadFileRequest();
        $validator = Validator::make(
            ['files' => [$file]],
            $request->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('files.0', $validator->errors()->toArray());
    }

    public function test_oversized_file_fails(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('huge.jpg', 200 * 1024, 'image/jpeg');

        $request = new UploadFileRequest();
        $validator = Validator::make(
            ['files' => [$file]],
            $request->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('files.0', $validator->errors()->toArray());
    }

    public function test_too_many_files_fails(): void
    {
        Storage::fake('public');

        $files = [];
        for ($i = 0; $i < 25; $i++) {
            $files[] = UploadedFile::fake()->create("file{$i}.jpg", 100, 'image/jpeg');
        }

        $request = new UploadFileRequest();
        $validator = Validator::make(
            ['files' => $files],
            $request->rules()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('files', $validator->errors()->toArray());
    }
}
