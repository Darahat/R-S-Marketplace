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
            'address_type' => 'required|in:shipping,billing',
            'full_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'street_address' => 'required|string|max:255',
            ///exists means that it will check to database that is the value is exist
            'district_id' => 'required|exists:districts,id',
            'upazila_id' => 'required|exists:upazilas,id',
            'union_id' => 'required|exists:unions,id',
            'postal_code' => 'required|string|max:20',
            'country' => 'required|string|max:100',
            /// in checks is the incoming value 1 or 0
            'is_default' => 'sometimes|boolean',
        ];
    }
    /*
    Prepare data for validation
    */
    protected function prepareForValidation()
    {
        /// Convert checkbox value to boolean
        $this->merge([
            'is_default' => $this->has('is_default') ? (bool) $this->is_default:false,
        ]);
     }

}
