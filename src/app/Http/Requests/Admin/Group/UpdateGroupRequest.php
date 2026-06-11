<?php

namespace App\Http\Requests\Admin\Group;

use Illuminate\Foundation\Http\FormRequest;

class UpdateGroupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        $id = $this->route('id');

        return [
            'name' => 'required|string|max:255',
            'alias' => 'nullable|string|max:255|unique:groups,alias,'.$id,
            'description' => 'nullable|string',
            'status' => 'nullable|boolean',
            'ordering' => 'nullable|integer',
        ];
    }
}
