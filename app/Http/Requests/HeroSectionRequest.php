<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HeroSectionRequest extends FormRequest
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
            'headline' => 'required|string|max:255',
            'highlight' => 'nullable|string|max:50',
            'subheadline' => 'required|string|max:500',
            'primary_text' => 'required|string|max:100',
            'primary_url' => 'required|string|max:255',
            'secondary_text' => 'nullable|string|max:100',
            'secondary_url' => 'nullable|string|max:255',
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
        ];
    }
}
