<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('orders')->insert([
            [
                'order_id' => 10005,
                'user_id' => 1,
                'billing_address_id' => 1,
                'shipping_address_id' => 1,
                'sub_total' => 1500.00,
                'discount' => 100.00,
                'total_amount' => 1400.00,
                'order_status' => 'pending',
                'created_at' => Carbon::parse('2025-05-01 10:00:00'),
            ],
            // Add more arrays as needed
            [
                                'order_id' => 10006,

                'user_id' => 1,
                'billing_address_id' => 2,
                'shipping_address_id' => 2,
                'sub_total' => 2000.00,
                'discount' => 200.00,
                'total_amount' => 1800.00,
                'order_status' => 'completed',
                'created_at' => Carbon::parse('2025-05-02 11:00:00'),
            ],
            [
                                'order_id' => 10007,

                'user_id' => 1,
                'billing_address_id' => 3,
                'shipping_address_id' => 3,
                'sub_total' => 2500.00,
                'discount' => 300.00,
                'total_amount' => 2200.00,
                'order_status' => 'shipped',
                'created_at' => Carbon::parse('2025-05-03 12:00:00'),
            ],
            [
                                'order_id' => 10008,

                'user_id' => 1,
                'billing_address_id' => 4,
                'shipping_address_id' => 4,
                'sub_total' => 3000.00,
                'discount' => 400.00,
                'total_amount' => 2600.00,
                'order_status' => 'delivered',
                'created_at' => Carbon::parse('2025-05-04 13:00:00'),
            ],
            [
                                'order_id' => 10009,

                'user_id' => 1,
                'billing_address_id' => 5,
                'shipping_address_id' => 5,
                'sub_total' => 3500.00,
                'discount' => 500.00,
                'total_amount' => 3000.00,
                'order_status' => 'returned',
                'created_at' => Carbon::parse('2025-05-05 14:00:00'),
            ],

            [
                                'order_id' => 100010,

                'user_id' => 1,
                'billing_address_id' => 6,
                'shipping_address_id' => 6,
                'sub_total' => 4000.00,
                'discount' => 600.00,
                'total_amount' => 3400.00,
                'order_status' => 'cancelled',
                'created_at' => Carbon::parse('2025-05-06 15:00:00'),
            ],
            [
                                'order_id' => 100011,

                'user_id' => 1,
                'billing_address_id' => 7,
                'shipping_address_id' => 7,
                'sub_total' => 4500.00,
                'discount' => 700.00,
                'total_amount' => 3800.00,
                'order_status' => 'pending',
                'created_at' => Carbon::parse('2025-05-07 16:00:00'),
            ],
            [
                                'order_id' => 100012,

                'user_id' => 1,
                'billing_address_id' => 8,
                'shipping_address_id' => 8,
                'sub_total' => 5000.00,
                'discount' => 800.00,
                'total_amount' => 4200.00,
                'order_status' => 'completed',
                'created_at' => Carbon::parse('2025-05-08 17:00:00'),
            ],
            [
                                'order_id' => 100016,

                'user_id' => 1,
                'billing_address_id' => 9,
                'shipping_address_id' => 9,
                'sub_total' => 5500.00,
                'discount' => 900.00,
                'total_amount' => 4600.00,
                'order_status' => 'shipped',
                'created_at' => Carbon::parse('2025-05-09 18:00:00'),
            ],
            [
                                'order_id' => 100015,

                'user_id' => 1,
                'billing_address_id' => 10,
                'shipping_address_id' => 10,
                'sub_total' => 6000.00,
                'discount' => 1000.00,
                'total_amount' => 5000.00,
                'order_status' => 'delivered',
                'created_at' => Carbon::parse('2025-05-10 19:00:00'),
            ],
            [
                                'order_id' => 100013,

                'user_id' => 1,
                'billing_address_id' => 11,
                'shipping_address_id' => 11,
                'sub_total' => 6500.00,
                'discount' => 1100.00,
                'total_amount' => 5400.00,
                'order_status' => 'returned',
                'created_at' => Carbon::parse('2025-05-11 20:00:00'),
            ],
            [
                                'order_id' => 100014,

                'user_id' => 1,
                'billing_address_id' => 12,
                'shipping_address_id' => 12,
                'sub_total' => 7000.00,
                'discount' => 1200.00,
                'total_amount' => 5800.00,
                'order_status' => 'cancelled',
                'created_at' => Carbon::parse('2025-05-12 21:00:00'),
            ],
        ]);
    }
   
}


 