<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserAddressRequests extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'address_type' => 'required|in:shipping,billing',
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'street_address' => 'required|string|max:255',
            'district_id' => 'required',
            'upazila_id' => 'required',
            'union_id' => 'required',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            'is_default' => 'required|in:1,0',
        ];
    }
}
