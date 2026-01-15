<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Stripe\PaymentMethod as StripePaymentMethod;
use App\Models\UserPaymentMethod;
class SetDefaultPaymentMethodRequest extends FormRequest
{
    protected $paymentMethodExists = null;
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {

    /*
    Check if authenticated user owns the payment method
     */
    $id = $this->input('id');

    /*
    If no ID is provided authorization fails
    */
    if(!$id){
        return false;
    }
    /*
    Check if the payment method exists and belongs to the authenticated user

    */
   $this->paymentMethodExists= UserPaymentMethod::where('user_id', Auth::id())
            ->where('id', $id)
            ->exists();
    return  $this->paymentMethodExists;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id' => [
                'required','integer',
                function($fail){
                    // Use Cached result from authorize()
                    if($this->paymentMethodExists === false){
                        $fail('You do not own this payment method or it does not exist');
                    }
                }
                ]

        ];
    }
}
