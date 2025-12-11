<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample categories with Unsplash image URLs
        $categories = [
            [
                'name' => 'Electronics',
                'slug' => 'electronics',
                'description' => 'Electronic devices and gadgets',
                'image' => 'https://images.unsplash.com/photo-1550355291-bbee04a92027?w=500&h=500&fit=crop',
                'status' => true,
                'is_featured' => true,
                'is_new' => false,
                'discount_price' => 0,
                'parent_id' => null,
                'created_by' => 1,
            ],
            [
                'name' => 'Computers',
                'slug' => 'computers',
                'description' => 'Desktop and laptop computers',
                'image' => 'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?w=500&h=500&fit=crop',
                'status' => true,
                'is_featured' => false,
                'is_new' => true,
                'discount_price' => 0,
                'parent_id' => 1,
                'created_by' => 1,
            ],
            [
                'name' => 'Smartphones',
                'slug' => 'smartphones',
                'description' => 'Mobile phones and smartphones',
                'image' => 'https://images.unsplash.com/photo-1511707267537-b85faf00021e?w=500&h=500&fit=crop',
                'status' => true,
                'is_featured' => true,
                'is_new' => false,
                'discount_price' => 0,
                'parent_id' => 1,
                'created_by' => 1,
            ],
            [
                'name' => 'Clothing',
                'slug' => 'clothing',
                'description' => 'Men and women clothing',
                'image' => 'https://images.unsplash.com/photo-1489824904134-891ab64532f1?w=500&h=500&fit=crop',
                'status' => true,
                'is_featured' => true,
                'is_new' => false,
                'discount_price' => 0,
                'parent_id' => null,
                'created_by' => 1,
            ],
            [
                'name' => 'Men\'s Wear',
                'slug' => 'mens-wear',
                'description' => 'Men clothing and accessories',
                'image' => 'https://images.unsplash.com/photo-1516992654631-3fbf8a0e7d5f?w=500&h=500&fit=crop',
                'status' => true,
                'is_featured' => false,
                'is_new' => false,
                'discount_price' => 0,
                'parent_id' => 4,
                'created_by' => 1,
            ],
            [
                'name' => 'Women\'s Wear',
                'slug' => 'womens-wear',
                'description' => 'Women clothing and accessories',
                'image' => 'https://images.unsplash.com/photo-1495521821757-a1efb6729352?w=500&h=500&fit=crop',
                'status' => true,
                'is_featured' => false,
                'is_new' => true,
                'discount_price' => 0,
                'parent_id' => 4,
                'created_by' => 1,
            ],
            [
                'name' => 'Books',
                'slug' => 'books',
                'description' => 'Fiction and non-fiction books',
                'image' => 'https://images.unsplash.com/photo-150784272343-583f20270319?w=500&h=500&fit=crop',
                'status' => true,
                'is_featured' => false,
                'is_new' => false,
                'discount_price' => 0,
                'parent_id' => null,
                'created_by' => 1,
            ],
            [
                'name' => 'Home & Kitchen',
                'slug' => 'home-kitchen',
                'description' => 'Kitchen appliances and home decor',
                'image' => 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=500&h=500&fit=crop',
                'status' => true,
                'is_featured' => false,
                'is_new' => false,
                'discount_price' => 0,
                'parent_id' => null,
                'created_by' => 1,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}