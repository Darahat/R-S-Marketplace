<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OrderTrackingFeatureTest extends TestCase
{
    use DatabaseTransactions;

    public function test_customer_can_view_order_history_with_own_orders(): void
    {
        $customer = User::factory()->create([
            'user_type' => User::CUSTOMER,
            'mobile' => '01720000001',
        ]);

        $order = Order::create([
            'user_id' => $customer->id,
            'order_number' => 'ORD-HISTORY-001',
            'order_status' => 'processing',
            'payment_method' => 'cash',
            'payment_status' => 'pending',
            'total_amount' => 1500,
        ]);

        $response = $this->actingAs($customer)->get(route('customer.orders'));

        $response->assertStatus(200);
        $response->assertSee($order->order_number);
        $response->assertSee(route('search'), false);
        $response->assertSee('name="q"', false);
    }

    public function test_customer_order_history_uses_real_order_item_preview_without_demo_placeholders(): void
    {
        $customer = User::factory()->create([
            'user_type' => User::CUSTOMER,
            'mobile' => '01720000011',
        ]);

        $category = Category::factory()->active()->create();
        $product = Product::factory()->create([
            'name' => 'History Preview Product',
            'slug' => 'history-preview-product',
            'category_id' => $category->id,
            'image' => null,
            'stock' => 10,
        ]);

        $order = Order::create([
            'user_id' => $customer->id,
            'order_number' => 'ORD-HISTORY-REAL-001',
            'order_status' => 'processing',
            'payment_method' => 'cash',
            'payment_status' => 'pending',
            'total_amount' => 1500,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 1500,
            'total' => 1500,
        ]);

        $response = $this->actingAs($customer)->get(route('customer.orders'));

        $response->assertStatus(200);
        $response->assertSee($order->order_number);
        $response->assertDontSee('via.placeholder.com', false);
        $response->assertSee(route('customer.order_details', [
            'orderNumber' => $order->order_number,
        ]), false);
    }

    public function test_customer_can_view_own_order_details_by_order_number(): void
    {
        $customer = User::factory()->create([
            'user_type' => User::CUSTOMER,
            'mobile' => '01720000002',
        ]);

        $category = Category::factory()->active()->create();
        $product = Product::factory()->create([
            'name' => 'Tracking Product',
            'slug' => 'tracking-product',
            'category_id' => $category->id,
            'stock' => 10,
        ]);

        $order = Order::create([
            'user_id' => $customer->id,
            'order_number' => 'ORD-DETAIL-001',
            'order_status' => 'to_pay',
            'payment_method' => 'cash',
            'payment_status' => 'pending',
            'total_amount' => 2000,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 2000,
            'total' => 2000,
        ]);

        $response = $this->actingAs($customer)->get(route('customer.order_details', [
            'orderNumber' => $order->order_number,
        ]));

        $response->assertStatus(200);
        $response->assertSee($order->order_number);
        $response->assertSee('Tracking Product');
        $response->assertSee(route('customer.orders'), false);
    }

    public function test_customer_cannot_view_other_customers_order_details(): void
    {
        $customerA = User::factory()->create([
            'user_type' => User::CUSTOMER,
            'mobile' => '01720000003',
        ]);

        $customerB = User::factory()->create([
            'user_type' => User::CUSTOMER,
            'mobile' => '01720000004',
        ]);

        $orderOfB = Order::create([
            'user_id' => $customerB->id,
            'order_number' => 'ORD-PRIVATE-001',
            'order_status' => 'processing',
            'payment_method' => 'cash',
            'payment_status' => 'pending',
            'total_amount' => 500,
        ]);

        $response = $this->actingAs($customerA)->get(route('customer.order_details', [
            'orderNumber' => $orderOfB->order_number,
        ]));

        $response->assertStatus(404);
    }
}
