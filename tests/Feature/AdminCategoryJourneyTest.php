<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminCategoryJourneyTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_can_view_categories_index(): void
    {
        $admin = User::factory()->create([
            'user_type' => User::ADMIN,
            'mobile' => '01910000001',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.categories.index'));

        $response->assertStatus(200);
        $response->assertViewIs('backend_panel_view_admin.pages.categories.index');
        $response->assertViewHas('categories');
    }

    public function test_admin_can_create_category(): void
    {
        $admin = User::factory()->create([
            'user_type' => User::ADMIN,
            'mobile' => '01910000002',
        ]);

        $payload = [
            'name' => 'Admin Category',
            'description' => 'Category created by test',
            'parent_id' => null,
            'status' => true,
            'is_featured' => false,
            'is_new' => false,
            'discount_price' => 0,
        ];

        $response = $this->actingAs($admin)->post(route('admin.categories.store'), $payload);

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('success', 'Category created successfully!');

        $this->assertDatabaseHas('categories', [
            'name' => 'Admin Category',
            'slug' => 'admin-category',
            'created_by' => $admin->id,
        ]);
    }

    public function test_admin_can_update_category(): void
    {
        $admin = User::factory()->create([
            'user_type' => User::ADMIN,
            'mobile' => '01910000003',
        ]);

        $category = Category::factory()->create([
            'name' => 'Old Category',
            'slug' => 'old-category',
            'created_by' => $admin->id,
        ]);

        $payload = [
            'name' => 'Updated Category',
            'description' => 'Updated description',
            'parent_id' => null,
            'status' => true,
            'is_featured' => true,
            'is_new' => false,
            'discount_price' => 10,
        ];

        $response = $this->actingAs($admin)->put(route('admin.categories.update', $category->id), $payload);

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('success', 'Category updated successfully!');

        $this->assertDatabaseHas('categories', [
            'id' => $category->id,
            'name' => 'Updated Category',
            'slug' => 'updated-category',
            'updated_by' => $admin->id,
        ]);
    }

    public function test_admin_can_toggle_category_status(): void
    {
        $admin = User::factory()->create([
            'user_type' => User::ADMIN,
            'mobile' => '01910000004',
        ]);

        $category = Category::factory()->create([
            'status' => true,
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.categories.toggleStatus', $category->id));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $category->refresh();
        $this->assertFalse((bool) $category->status);
    }

    public function test_admin_can_toggle_category_featured_status(): void
    {
        $admin = User::factory()->create([
            'user_type' => User::ADMIN,
            'mobile' => '01910000005',
        ]);

        $category = Category::factory()->create([
            'is_featured' => false,
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->post(route('admin.categories.toggleFeatured', $category->id));

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $category->refresh();
        $this->assertTrue((bool) $category->is_featured);
    }

    public function test_admin_can_delete_category_without_children_or_products(): void
    {
        $admin = User::factory()->create([
            'user_type' => User::ADMIN,
            'mobile' => '01910000006',
        ]);

        $category = Category::factory()->create([
            'created_by' => $admin->id,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.categories.destroy', $category->id));

        $response->assertRedirect(route('admin.categories.index'));
        $response->assertSessionHas('success', 'Category deleted successfully!');
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_admin_cannot_delete_category_with_associated_products(): void
    {
        $admin = User::factory()->create([
            'user_type' => User::ADMIN,
            'mobile' => '01910000007',
        ]);

        $category = Category::factory()->create([
            'created_by' => $admin->id,
        ]);

        Product::factory()->create([
            'category_id' => $category->id,
            'stock' => 5,
        ]);

        $response = $this->actingAs($admin)->delete(route('admin.categories.destroy', $category->id));

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Cannot delete category with associated products!');
        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }
}
