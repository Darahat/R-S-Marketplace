<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true); // "Electronics Devices"

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->paragraphs(2, true),
            'image' => fake()->optional(0.7)->imageUrl(640, 480, 'category'), // 70% chance
            'status' => fake()->boolean(90), // 90% active
            'is_featured' => fake()->boolean(30), // 30% featured
            'is_new' => fake()->boolean(20), // 20% new
            'discount_price' => fake()->boolean(40) ? fake()->randomFloat(2, 5, 50) : 0, // 40% chance of discount
            'parent_id' => null, // Default to top-level
            'created_by' => User::factory(),
            'updated_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the category is active.
     */
    public function active(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'status' => true,
        ]);
    }

    /**
     * Indicate that the category is inactive.
     */
    public function inactive(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'status' => false,
        ]);
    }

    /**
     * Indicate that the category is featured.
     */
    public function featured(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'is_featured' => true,
        ]);
    }



    /**
     * Indicate that the category has a parent.
     */
    public function withParent(): Factory
    {
        return $this->state(function (array $attributes) {
            $parent = Category::whereNull('parent_id')->inRandomOrder()->first()
                    ?: Category::factory()->create(['parent_id' => null]);

            return [
                'parent_id' => $parent->id,
            ];
        });
    }

    /**
     * Indicate that the category is a top-level category (no parent).
     */
    public function topLevel(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => null,
        ]);
    }

    /**
     * Indicate that the category has a specific parent.
     */
    public function forParent(Category $parent): Factory
    {
        return $this->state(fn (array $attributes) => [
            'parent_id' => $parent->id,
        ]);
    }

    /**
     * Indicate that the category has a discount.
     */
    public function withDiscount(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'discount_price' => fake()->randomFloat(2, 10, 100),
        ]);
    }

    /**
     * Indicate that the category has no discount.
     */
    public function withoutDiscount(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'discount_price' => 0.00,
        ]);
    }

    /**
     * Indicate that the category has an image.
     */
    public function withImage(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'image' => fake()->imageUrl(640, 480, 'category', true, $attributes['name']),
        ]);
    }

    /**
     * Indicate that the category has no image.
     */
    public function withoutImage(): Factory
    {
        return $this->state(fn (array $attributes) => [
            'image' => null,
        ]);
    }

    /**
     * Create a category tree hierarchy.
     */
    public function withChildren($depth = 1): Factory
    {
        return $this->afterCreating(function (Category $category) use ($depth) {
            if ($depth > 0) {
                Category::factory()
                    ->count(fake()->numberBetween(2, 5))
                    ->forParent($category)
                    ->withChildren($depth - 1)
                    ->create();
            }
        });
    }
}
