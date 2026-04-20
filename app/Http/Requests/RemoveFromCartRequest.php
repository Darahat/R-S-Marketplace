<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RemoveFromCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'item' => 'required|integer|exists:products,id',
        ];
    }

    public function messages(): array
    {
        return [
            'item.exists' => 'The selected product does not exist.',
        ];
    }
}
