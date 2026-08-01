<?php

namespace Modules\MediaManager\Infrastructure\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Modules\MediaManager\Application\DTO\CreateFolderData;

class CreateFolderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows('manage-media');
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-zA-Zа-яА-Я0-9_\-\.]+$/u',
                'regex:/^(?!CON\b|AUX\b|NUL\b|PRN\b|COM[1-9]\b|LPT[1-9]\b)/i',
                function ($attribute, $value, $fail) {
                    $basename = pathinfo($value, PATHINFO_FILENAME);

                    $reserved = [
                        'con', 'aux', 'nul', 'prn',
                        'com1', 'com2', 'com3', 'com4', 'com5', 'com6', 'com7', 'com8', 'com9',
                        'lpt1', 'lpt2', 'lpt3', 'lpt4', 'lpt5', 'lpt6', 'lpt7', 'lpt8', 'lpt9',
                    ];

                    if (in_array(strtolower($basename), $reserved, true)) {
                        $fail('Имя является зарезервированным системным именем.');
                        return;
                    }

                    if (str_starts_with($value, '.')) {
                        $fail('Имя не может начинаться с точки.');
                        return;
                    }

                    if (str_contains($value, '..')) {
                        $fail('Имя не может содержать двойные точки.');
                        return;
                    }

                    $forbidden = ['\\', '/', '|', '<', '>', ':', '"', '?', '*'];
                    foreach ($forbidden as $char) {
                        if (str_contains($value, $char)) {
                            $fail('Имя содержит запрещённый символ: ' . $char);
                            return;
                        }
                    }
                },
            ],
            'path' => ['nullable', 'string'],
        ];
    }

    public function toDTO(): CreateFolderData
    {
        return new CreateFolderData(
            name: $this->input('name'),
            path: (string) $this->input('path', ''),
        );
    }
}
