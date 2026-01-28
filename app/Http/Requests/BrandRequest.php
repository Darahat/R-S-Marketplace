<?php

namespace App\Http\Requests;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class BrandRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        if($this->isMethod('get')){
            return true;
        }
        $user = $this->user();
        //For POST, PUT, PATCH, Delete
        return $user && $user->isAdmin();
    }

    /**
     *
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:brands',
            'category_id' => 'nullable|array|exists:categories,id',
            'status' => 'nullable|in:1',
        ];
    }
}
