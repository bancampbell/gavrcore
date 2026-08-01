<?php

namespace Modules\MediaManager\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Modules\MediaManager\Application\DTO\UploadFileData;

class UploadFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage-media');
    }

    public function rules(): array
    {
        $allowedMimetypes = config('media-manager.allowed_mimetypes', [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/svg+xml',
            'image/webp',
            'application/pdf',
        ]);

        $maxFileSize = config('media-manager.max_file_size', 100 * 1024); // KB
        $maxUploadFiles = config('media-manager.max_upload_files', 20);

        return [
            'files' => ['required', 'array', 'max:' . $maxUploadFiles],
            'files.*' => [
                'required',
                'file',
                'mimetypes:' . implode(',', $allowedMimetypes),
                'max:' . $maxFileSize,
            ],
            'path' => ['nullable', 'string'],
        ];
    }

    public function toDTO(): UploadFileData
    {
        $filePaths = [];
        foreach ($this->file('files', []) as $file) {
            if ($file->isValid()) {
                $filePaths[] = $file->getRealPath();
            }
        }

        return new UploadFileData(
            filePaths: $filePaths,
            path: (string) $this->input('path', ''),
        );
    }
}
