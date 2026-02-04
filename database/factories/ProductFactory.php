<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(3, true); // "Wireless Bluetooth Headphones"

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->paragraphs(3, true),
            'price' => fake()->randomFloat(2, 100, 1000),
            'discount_price' => fake()->boolean(30) ? fake()->randomFloat(2, 10, 300) : 0, // 30% chance of discount
            'sold_count' => fake()->numberBetween(0, 5000),
            'featured' => fake()->boolean(20), // 20% chance
            'image' => fake()->optional()->imageUrl(640, 480, 'product'),
            'category_id' => Category::factory(),
            'stock' => fake()->numberBetween(0, 1000),
            'is_best_selling' => fake()->boolean(15),
            'is_latest' => fake()->boolean(30),
            'is_flash_sale' => fake()->boolean(10),
            'is_todays_deal' => fake()->boolean(5),
            'rating' => fake()->randomFloat(1, 1, 5),
        ];
    }

    /**
     * Indicate that the product is discounted.
     */
    public function discounted(): Factory
    {
        return $this->state(function (array $attributes) {
            $price = $attributes['price'] ?? fake()->randomFloat(2, 100, 1000);
            $discount = $price * fake()->randomFloat(2, 0.1, 0.5); // 10-50% discount

            return [
                'discount_price' => round($price - $discount, 2),
            ];
        });
    }

    /**
     * Indicate that the product is out of stock.
     */
    public function outOfStock(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'stock' => 0,
        ]);
    }

    /**
     * Indicate that the product is featured.
     */
    public function featured(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'featured' => true,
        ]);
    }

    /**
     * Indicate that the product is a best seller.
     */
    public function bestSeller(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'is_best_selling' => true,
            'sold_count' => fake()->numberBetween(1000, 10000),
        ]);
    }

    /**
     * Indicate that the product has no discount.
     */
    public function noDiscount(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'discount_price' => 0.00,
        ]);
    }

    /**
     * Indicate the product belongs to a specific category.
     */
    public function forCategory(Category $category): Factory
    {
        return $this->state(fn (array $attributes) => [
            'category_id' => $category->id,
        ]);
    }

    /**
     * Indicate the product belongs to a specific brand.
     */
    public function forBrand(Brand $brand): Factory
    {
        return $this->state(fn (array $attributes) => [
            'brand_id' => $brand->id,
        ]);
    }
}
