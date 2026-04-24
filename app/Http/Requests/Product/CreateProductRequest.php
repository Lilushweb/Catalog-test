<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class CreateProductRequest extends FormRequest
{
    public function rules()
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'price' => ['required', 'numeric', 'gt:0', 'decimal:0,2'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
        ];
    }

    public function authorize()
    {
        return true;
    }
}
