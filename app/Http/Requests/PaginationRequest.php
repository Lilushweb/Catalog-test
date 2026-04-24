<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaginationRequest extends FormRequest
{
    public function rules()
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:10', 'max:15'],
            'category_id' => ['sometimes', 'integer', 'exists:categories,id'],
        ];
    }

    public function authorize()
    {
        return true;
    }
}
