<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoryIds = DB::table('categories')->pluck('id')->toArray();

        $brands = [
            'Samsung', 'Apple', 'Sony', 'LG', 'HP',
            'Dell', 'Lenovo', 'Asus', 'Nike', 'Adidas',
        ];

        foreach ($brands as $name) {
            // Assign 1-3 random category IDs
            $assignedIds = collect($categoryIds)->random(min(rand(1, 3), count($categoryIds)))->implode(',');

            Brand::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'slug' => Str::slug($name),
                    'category_id' => $assignedIds,
                    'status' => true,
                ]
            );
        }
    }
}
