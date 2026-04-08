<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BrowseFeatureTest extends TestCase
{
    use DatabaseTransactions;

    public function test_guest_can_view_home_page(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
    }

    public function test_guest_can_view_category_page_by_slug(): void
    {
        $category = Category::factory()->active()->create([
            'name' => 'Electronics',
            'slug' => 'electronics',
        ]);

        Product::factory()->create([
            'name' => 'Demo Phone',
            'slug' => 'demo-phone',
            'category_id' => $category->id,
            'stock' => 10,
        ]);

        $response = $this->get(route('category', ['slug' => $category->slug]));

        $response->assertStatus(200);
        $response->assertSee('Electronics');
    }

    public function test_guest_can_view_product_details_page(): void
    {
        $category = Category::factory()->active()->create();

        $product = Product::factory()->create([
            'name' => 'Gaming Laptop Pro',
            'slug' => 'gaming-laptop-pro',
            'category_id' => $category->id,
            'stock' => 8,
        ]);

        $response = $this->get(route('product', ['slug' => $product->slug]));

        $response->assertStatus(200);
        $response->assertSee('Gaming Laptop Pro');
    }

    public function test_guest_can_search_products_by_query(): void
    {
        $category = Category::factory()->active()->create();

        Product::factory()->create([
            'name' => 'Ultra Search Laptop',
            'slug' => 'ultra-search-laptop',
            'category_id' => $category->id,
            'stock' => 7,
        ]);

        Product::factory()->create([
            'name' => 'Unrelated Mouse',
            'slug' => 'unrelated-mouse',
            'category_id' => $category->id,
            'stock' => 9,
        ]);

        $response = $this->get(route('search', ['q' => 'Laptop']));

        $response->assertStatus(200);
        $response->assertSee('Ultra Search Laptop');
    }
}
