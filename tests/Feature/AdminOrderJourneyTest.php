<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class AdminOrderJourneyTest extends TestCase
{
    use RefreshDatabase;

    private function createOrderForUser(User $user): Order
    {
        $order = Order::create([
            'user_id' => $user->id,
            'order_number' => 'ORD-ADMIN-' . uniqid(),
            'order_status' => 'pending',
            'payment_method' => 'cash',
            'payment_status' => 'pending',
            'total_amount' => 1000,
        ]);

        $product = Product::factory()->create([
            'stock' => 10,
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 1000,
            'total' => 1000,
        ]);

        Payment::create([
            'order_id' => $order->id,
            'user_id' => $user->id,
            'transaction_id' => 'TXN-' . uniqid(),
            'payment_method' => 'cash',
            'payment_status' => 'pending',
            'amount' => 1000,
        ]);

        return $order;
    }

    public function test_admin_can_view_orders_index(): void
    {
        $admin = User::factory()->create([
            'user_type' => User::ADMIN,
            'mobile' => '01610000001',
        ]);

        $customer = User::factory()->create([
            'user_type' => User::CUSTOMER,
            'mobile' => '01610000002',
        ]);

        $order = $this->createOrderForUser($customer);

        $response = $this->actingAs($admin)->get(route('admin.orders'));

        $response->assertStatus(200);
        $response->assertViewIs('backend_panel_view_admin.pages.orders.index');
        $response->assertSee($order->order_number);
    }

    public function test_admin_can_view_single_order(): void
    {
        $admin = User::factory()->create([
            'user_type' => User::ADMIN,
            'mobile' => '01610000003',
        ]);

        $customer = User::factory()->create([
            'user_type' => User::CUSTOMER,
            'mobile' => '01610000004',
        ]);

        $order = $this->createOrderForUser($customer);

        $response = $this->actingAs($admin)->get(route('admin.orders.show', $order->id));

        $response->assertStatus(200);
        $response->assertViewIs('backend_panel_view_admin.pages.orders.show');
        $response->assertSee($order->order_number);
    }

    public function test_admin_can_update_order_status(): void
    {
        Queue::fake();

        $admin = User::factory()->create([
            'user_type' => User::ADMIN,
            'mobile' => '01610000005',
        ]);

        $customer = User::factory()->create([
            'user_type' => User::CUSTOMER,
            'mobile' => '01610000006',
        ]);

        $order = $this->createOrderForUser($customer);

        $response = $this->actingAs($admin)->post(route('admin.orders.update-status', $order->id), [
            'status' => 'processing',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'order_status' => 'processing',
        ]);
    }

    public function test_admin_can_update_payment_status(): void
    {
        $admin = User::factory()->create([
            'user_type' => User::ADMIN,
            'mobile' => '01610000007',
        ]);

        $customer = User::factory()->create([
            'user_type' => User::CUSTOMER,
            'mobile' => '01610000008',
        ]);

        $order = $this->createOrderForUser($customer);

        $response = $this->actingAs($admin)->post(route('admin.orders.update-payment-status', $order->id), [
            'payment_status' => 'paid',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'paid',
        ]);
    }

    public function test_admin_can_update_order_notes(): void
    {
        $admin = User::factory()->create([
            'user_type' => User::ADMIN,
            'mobile' => '01610000009',
        ]);

        $customer = User::factory()->create([
            'user_type' => User::CUSTOMER,
            'mobile' => '01610000010',
        ]);

        $order = $this->createOrderForUser($customer);

        $response = $this->actingAs($admin)->post(route('admin.orders.update-notes', $order->id), [
            'notes' => 'Packed and ready for dispatch',
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'notes' => 'Packed and ready for dispatch',
        ]);
    }

    public function test_admin_can_view_print_invoice_page(): void
    {
        $admin = User::factory()->create([
            'user_type' => User::ADMIN,
            'mobile' => '01610000011',
        ]);

        $customer = User::factory()->create([
            'user_type' => User::CUSTOMER,
            'mobile' => '01610000012',
        ]);

        $order = $this->createOrderForUser($customer);

        $response = $this->actingAs($admin)->get(route('admin.orders.print', $order->id));

        $response->assertStatus(200);
        $response->assertViewIs('backend_panel_view_admin.pages.orders.invoice');
    }
}
