<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Database\Seeders\Concerns\ColumnSafeSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CategorySeeder extends Seeder
{
    use ColumnSafeSeeder;

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

        $now = Carbon::now();

        foreach ($categories as $index => $category) {
            $category['updated_by'] = $category['created_by'] ?? null;
            $category['created_at'] = $now;
            $category['updated_at'] = $now;

            $safeRow = $this->filterRowByTable('categories', $category);

            if (empty($safeRow)) {
                continue;
            }

            DB::table('categories')->updateOrInsert(
                ['slug' => $categories[$index]['slug']],
                $safeRow
            );
        }
    }
}
