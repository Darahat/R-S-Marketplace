<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Step 1 (checkout review) only collects shipping and notes.
            'address_id' => 'required|exists:addresses,id',
            'is_buy_now' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ];
    }
}
