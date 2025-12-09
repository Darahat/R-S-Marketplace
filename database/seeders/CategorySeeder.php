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
        // Sample categories - match migration schema
        $categories = [
            [
                'name' => 'Electronics',
                'slug' => 'electronics',
                'description' => 'Electronic devices and gadgets',
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
