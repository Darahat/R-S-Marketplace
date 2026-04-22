<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class BrandRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     *
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $brandId = $this->route('id');
        return [
            'name' => 'required|string|max:255|unique:brands,name,' . ($brandId ?? 'NULL'),
            'slug' => 'required|string|max:255|unique:brands,slug,' . ($brandId ?? 'NULL'),
            'logo' => 'nullable|file|mimes:jpg,jpeg,png,gif,webp,bmp,avif|max:4096',
            'category_id' => 'nullable|array',
            'category_id.*' => 'exists:categories,id',
            'status' => 'required|boolean',
        ];
    }
    protected function prepareForValidation(): void
    {
        // Auto-generate slug from name if not provided
        if ($this->has('name') && empty($this->slug)) {
            $this->merge([
                'slug' => \Illuminate\Support\Str::slug($this->name),
            ]);
        }

        // Convert checkbox to boolean
        if ($this->has('status')) {
            $this->merge([
                'status' => filter_var($this->status, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }
}
