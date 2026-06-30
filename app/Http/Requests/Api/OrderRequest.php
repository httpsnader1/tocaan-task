<?php

namespace App\Http\Requests\Api;

use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'products.*.product_id' => ['required', Rule::exists(Product::class, 'id')],
            'products.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
