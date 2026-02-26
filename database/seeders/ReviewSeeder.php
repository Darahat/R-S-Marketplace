<?php

namespace Database\Seeders;
use Database\Seeders\Concerns\ColumnSafeSeeder;
use Illuminate\Support\Facades\DB;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Arr;

class ReviewSeeder extends Seeder
{
    use ColumnSafeSeeder;

    public function run(): void
    {
        $users = User::take(3)->pluck('id')->toArray();
        $products = Product::pluck('id')->toArray();

        if (empty($users) || empty($products)) {
            return;
        }

        $comments = [
            'Fantastic product, exceeded my expectations!',
            'Good value for the price.',
            'Works as advertised, happy with the purchase.',
            'Build quality is solid and looks great.',
            'Battery life could be better but still solid.',
            'Highly recommend to friends and family.',
            'Customer service was responsive and helpful.',
        ];

        $data = [];
        foreach ($products as $productId) {
            $data[] = [
                'user_id' => Arr::random($users),
                'product_id' => $productId,
                'rating' => rand(4, 5),
                'comment' => Arr::random($comments),
                'is_verified' => true,
                'created_at' => now()->subDays(rand(1, 30)),
                'updated_at' => now()->subDays(rand(1, 30)),
            ];
        }

        $safeData = $this->filterRowsByTable('reviews', $data);

        if (!empty($safeData)) {
            DB::table('reviews')->insert($safeData);
        }
    }
}
