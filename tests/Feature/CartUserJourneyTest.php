<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartUserJourneyTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_add_valid_product_to_cart_and_view_cart_page(): void
    {
        $category = Category::factory()->active()->create();

        $product = Product::factory()->create([
            'name' => 'Cart Product',
            'slug' => 'cart-product',
            'category_id' => $category->id,
            'stock' => 10,
        ]);

        $response = $this->from(route('home'))->post(route('cart.add'), [
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('success', 'Product added to cart!');

        $sessionCart = session('cart', []);
        $this->assertArrayHasKey($product->id, $sessionCart);
        $this->assertEquals(2, $sessionCart[$product->id]['quantity']);

        $cartPage = $this->get(route('cart.view'));
        $cartPage->assertStatus(200);
        $cartPage->assertSee('Cart Product');
    }

    public function test_guest_cannot_add_invalid_product_id_to_cart(): void
    {
        $response = $this->from(route('cart.view'))->post(route('cart.add'), [
            'product_id' => 999999,
            'quantity' => 1,
        ]);

        $response->assertRedirect(route('cart.view'));
        $response->assertSessionHasErrors('product_id');
        $this->assertEmpty(session('cart', []));
    }

    public function test_authenticated_user_with_empty_cart_is_redirected_from_checkout(): void
    {
        $user = User::factory()->create([
            'user_type' => User::CUSTOMER,
            'mobile' => '01710000001',
        ]);

        $response = $this->actingAs($user)->get(route('checkout'));

        $response->assertRedirect(route('cart.view'));
        $response->assertSessionHas('error', 'Your cart is empty');
    }

    public function test_authenticated_user_with_cart_can_access_checkout_page(): void
    {
        $user = User::factory()->create([
            'user_type' => User::CUSTOMER,
            'mobile' => '01710000002',
        ]);

        $category = Category::factory()->active()->create();
        $product = Product::factory()->create([
            'name' => 'Checkout Product',
            'slug' => 'checkout-product',
            'category_id' => $category->id,
            'stock' => 5,
        ]);

        $cart = Cart::create(['user_id' => $user->id]);
        CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => $product->price,
        ]);

        $response = $this->actingAs($user)->get(route('checkout'));

        $response->assertStatus(200);
        $response->assertSee('Checkout - Step 1: Shipping Details');
    }
}
