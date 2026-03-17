<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;

class CompletePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Ensure the order belongs to the authenticated user
        $orderNumber = $this->route('orderNumber');
        return Order::where('order_number', $orderNumber)
            ->where('user_id', Auth::id())
            ->exists();
    }

    public function rules(): array
    {
        return [
            'payment_method' => 'required|string|in:cash,bkash,card',
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method.in' => 'Payment method must be cash, bkash, or card.',
        ];
    }
}
