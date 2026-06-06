<?php

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;

class UserIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => 'nullable|string',
            'blocked' => 'nullable|boolean',
            'activated' => 'nullable|boolean',
            'page' => 'nullable|integer',
            'per_page' => 'nullable|integer',
        ];
    }

    protected function prepareForValidation()
    {
        // Преобразуем строковые значения 'true'/'false' в boolean
        if ($this->has('blocked')) {
            $blocked = $this->input('blocked');
            if ($blocked === 'true' || $blocked === '1' || $blocked === 1) {
                $this->merge(['blocked' => true]);
            } elseif ($blocked === 'false' || $blocked === '0' || $blocked === 0) {
                $this->merge(['blocked' => false]);
            }
        }

        if ($this->has('activated')) {
            $activated = $this->input('activated');
            if ($activated === 'true' || $activated === '1' || $activated === 1) {
                $this->merge(['activated' => true]);
            } elseif ($activated === 'false' || $activated === '0' || $activated === 0) {
                $this->merge(['activated' => false]);
            }
        }
    }
}
