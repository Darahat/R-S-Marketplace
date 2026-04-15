<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BulkDeleteProductsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'ids' => 'required|array|min:1',
            'ids.*' => 'required|integer|distinct|exists:products,id',
        ];
    }
}
