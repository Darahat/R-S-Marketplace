<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\Concerns\ColumnSafeSeeder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    use ColumnSafeSeeder;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $customer = User::query()->where('user_type', 'CUSTOMER')->first() ?? User::query()->first();

        if (!$customer) {
            return;
        }

        $addressId = Address::query()->where('user_id', $customer->id)->value('id');

        $statuses = ['pending', 'processing', 'shipped', 'delivered', 'returned', 'cancelled'];
        $paymentMethods = ['cod', 'cash', 'card', 'bkash', 'stripe'];
        $paymentStatuses = ['pending', 'unpaid', 'paid', 'failed'];

        for ($i = 1; $i <= 12; $i++) {
            $subtotal = 1000 + ($i * 250);
            $shipping = 80;
            $tax = round($subtotal * 0.05, 2);
            $discount = $i % 3 === 0 ? 100 : 0;
            $total = $subtotal + $shipping + $tax - $discount;
            $createdAt = Carbon::parse('2025-05-01 10:00:00')->addDays($i - 1);

            $orderNumber = sprintf('ORD-202505-%04d', 1000 + $i);

            $row = $this->filterRowByTable('orders', [
                'user_id' => $customer->id,
                'order_number' => $orderNumber,
                'address_id' => $addressId,
                'order_status' => $statuses[($i - 1) % count($statuses)],
                'payment_method' => $paymentMethods[($i - 1) % count($paymentMethods)],
                'payment_status' => $paymentStatuses[($i - 1) % count($paymentStatuses)],
                'subtotal' => $subtotal,
                'shipping_cost' => $shipping,
                'tax' => $tax,
                'discount' => $discount,
                'total' => $total,
                'total_amount' => $total,
                'notes' => 'Seeder generated order',
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            if (!empty($row)) {
                DB::table('orders')->updateOrInsert(
                    ['order_number' => $orderNumber],
                    $row
                );
            }
        }
    }
}
