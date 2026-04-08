<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminProductJourneyTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_can_view_products_index(): void
    {
        $admin = User::factory()->create([
            'user_type' => User::ADMIN,
            'mobile' => '01810000001',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.products.index'));

        $response->assertStatus(200);
        $response->assertViewIs('backend_panel_view_admin.pages.products.index');
        $response->assertViewHas('products');
    }

    public function test_admin_can_create_product(): void
    {
        $admin = User::factory()->create([
            'user_type' => User::ADMIN,
            'mobile' => '01810000002',
        ]);

        $category = Category::factory()->active()->create();

        $payload = [
            'name' => 'Admin Created Product',
            'slug' => 'admin-created-product',
            'description' => 'Test product description',
            'price' => 1200,
            'purchase_price' => 900,
            'discount_price' => 1000,
            'stock' => 10,
            'category_id' => $category->id,
            'brand_id' => null,
            'featured' => true,
            'is_latest' => true,
        ];

        $response = $this->actingAs($admin)->post(route('admin.products.store'), $payload);

        $response->assertRedirect(route('admin.products.index'));
        $response->assertSessionHas('success', 'Product created successfully!');

        $this->assertDatabaseHas('products', [
            'name' => 'Admin Created Product',
            'slug' => 'admin-created-product',
            'category_id' => $category->id,
        ]);
    }

    public function test_admin_can_update_product(): void
    {
        $admin = User::factory()->create([
            'user_type' => User::ADMIN,
            'mobile' => '01810000003',
        ]);

        $category = Category::factory()->active()->create();
        $product = Product::factory()->create([
            'name' => 'Old Product Name',
            'slug' => 'old-product-name',
            'category_id' => $category->id,
            'stock' => 8,
        ]);

        $payload = [
            'name' => 'Updated Product Name',
            'slug' => 'updated-product-name',
            'description' => 'Updated description',
            'price' => 1400,
            'purchase_price' => 1000,
            'discount_price' => 1200,
            'stock' => 15,
            'category_id' => $category->id,
            'brand_id' => null,
            'featured' => false,
            'is_latest' => true,
        ];

        $response = $this->actingAs($admin)->put(route('admin.products.update', $product->id), $payload);

        $response->assertRedirect(route('admin.products.index'));
        $response->assertSessionHas('success', 'Product updated successfully!');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Product Name',
            'slug' => 'updated-product-name',
            'stock' => 15,
        ]);
    }

    public function test_admin_can_toggle_product_featured_status(): void
    {
        $admin = User::factory()->create([
            'user_type' => User::ADMIN,
            'mobile' => '01810000004',
        ]);

        $category = Category::factory()->active()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'featured' => false,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.products.toggleFeatured', $product->id));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $product->refresh();
        $this->assertTrue((bool) $product->featured);
    }

    public function test_admin_can_delete_product(): void
    {
        $admin = User::factory()->create([
            'user_type' => User::ADMIN,
            'mobile' => '01810000005',
        ]);

        $category = Category::factory()->active()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.products.destroy', $product->id));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }

    public function test_admin_can_bulk_delete_products(): void
    {
        $admin = User::factory()->create([
            'user_type' => User::ADMIN,
            'mobile' => '01810000006',
        ]);

        $category = Category::factory()->active()->create();
        $productA = Product::factory()->create(['category_id' => $category->id]);
        $productB = Product::factory()->create(['category_id' => $category->id]);

        $response = $this->actingAs($admin)->post(route('admin.products.bulk-delete'), [
            'ids' => [$productA->id, $productB->id],
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseMissing('products', ['id' => $productA->id]);
        $this->assertDatabaseMissing('products', ['id' => $productB->id]);
    }
}
