<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class RegistrarRequest extends FormRequest
{
    /**
     * Determine if the user not authorize can access
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

            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'mobile' => 'required|string|max:15',

        ];
    }
    /// if failed to validation
    public function failedValidation(Validator $validator)
    {
          if ($this->ajax() || $this->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation Failed',
                'errors' => [
                    'email' => $validator->errors(),
                ]
            ], 422);
        }
        return parent::failedValidation($validator);
    }
}
