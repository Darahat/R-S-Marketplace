<?php

namespace App\Http\Requests;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $productId = $this->route('id') ?? $this->route('product');

        return [
            'name' => 'required|string|max:255|unique:products,name,' . ($productId ?? 'NULL'),
            'slug' => 'nullable|string|max:255|unique:products,slug,' . ($productId ?? 'NULL'),
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'purchase_price' => 'nullable|numeric|min:0',
            'discount_price' => 'nullable|numeric|min:0|lt:price',
            'stock' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'image' => $this->isMethod('POST')
                ? 'required|file|mimes:jpeg,png,jpg,gif,webp,bmp,avif|max:5120'
                : 'nullable|file|mimes:jpeg,png,jpg,gif,webp,bmp,avif|max:5120',
            'rating' => 'nullable|numeric|min:0|max:5',
            'featured' => 'nullable|boolean',
            'is_best_selling' => 'nullable|boolean',
            'is_latest' => 'nullable|boolean',
            'is_flash_sale' => 'nullable|boolean',
            'is_todays_deal' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Product name is required',
            'name.unique' => 'A product with this name already exists',
            'name.max' => 'Product name must not exceed 255 characters',
            'price.required' => 'Price is required',
            'price.numeric' => 'Price must be a valid number',
            'price.min' => 'Price cannot be negative',
            'discount_price.lt' => 'Discount price must be less than the regular price',
            'stock.required' => 'Stock quantity is required',
            'stock.integer' => 'Stock must be a whole number',
            'stock.min' => 'Stock cannot be negative',
            'category_id.required' => 'Please select a category',
            'category_id.exists' => 'Selected category does not exist',
            'brand_id.exists' => 'Selected brand does not exist',
            'image.required' => 'Product image is required',
            'image.file' => 'File upload is invalid',
            'image.mimes' => 'Image must be jpg, jpeg, png, gif, webp, bmp, or avif',
            'image.max' => 'Image size must not exceed 5MB',
            'rating.numeric' => 'Rating must be a number',
            'rating.min' => 'Rating cannot be less than 0',
            'rating.max' => 'Rating cannot exceed 5',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert checkbox values to boolean
        $booleanFields = ['featured', 'is_best_selling', 'is_latest', 'is_flash_sale', 'is_todays_deal'];

        $merge = [];
        foreach ($booleanFields as $field) {
            if ($this->has($field)) {
                $merge[$field] = filter_var($this->input($field), FILTER_VALIDATE_BOOLEAN);
            } else {
                $merge[$field] = false;
            }
        }

        $this->merge($merge);
    }
}
