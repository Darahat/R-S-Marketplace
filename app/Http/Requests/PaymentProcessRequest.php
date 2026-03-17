<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentProcessRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'payment_method'         => 'required|string|in:cash,bkash,stripe',
            'pay_subscription'       => 'nullable|in:0,1',
            'saved_payment_method_id' => 'nullable|string',
            'save_payment_card'      => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method.in' => 'Payment method must be cash, bkash, or stripe.',
        ];
    }
}
