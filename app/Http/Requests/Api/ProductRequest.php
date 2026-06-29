<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'price' => ['required', 'numeric'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => filter_var($this->input('is_active'), FILTER_VALIDATE_BOOLEAN),
        ]);
    }
}
