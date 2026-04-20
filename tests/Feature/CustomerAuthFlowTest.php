<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class CustomerAuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_register_page(): void
    {
        $response = $this->get(route('register'));

        $response->assertStatus(200);
    }

    public function test_guest_can_register_successfully(): void
    {
        Queue::fake();

        $payload = [
            'name' => 'Test Customer',
            'email' => 'customer_register@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'mobile' => '01700000000',
        ];

        $response = $this->post(route('register'), $payload);

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('success', 'Registration successful!');

        $this->assertDatabaseHas('users', [
            'email' => 'customer_register@example.com',
            'name' => 'Test Customer',
            'user_type' => User::CUSTOMER,
        ]);
        $this->assertAuthenticated();
    }

    public function test_register_requires_mobile_and_password_confirmation(): void
    {
        $payload = [
            'name' => 'Test Customer',
            'email' => 'invalid_register@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different-password',
        ];

        $response = $this->from(route('register'))->post(route('register'), $payload);

        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors(['mobile', 'password']);
        $this->assertGuest();
    }

    public function test_guest_can_view_login_page(): void
    {
        $response = $this->get(route('login'));

        $response->assertRedirect(route('home', ['auth' => 'login']));
    }

    public function test_customer_can_login_with_valid_credentials(): void
    {
        $user = User::factory()->create([
            'email' => 'customer_login@example.com',
            'password' => Hash::make('password123'),
            'user_type' => User::CUSTOMER,
            'mobile' => '01700000001',
        ]);

        $response = $this->post(route('checklogin'), [
            'email' => 'customer_login@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_customer_cannot_login_with_invalid_credentials(): void
    {
        User::factory()->create([
            'email' => 'invalid_login@example.com',
            'password' => Hash::make('password123'),
            'user_type' => User::CUSTOMER,
            'mobile' => '01700000002',
        ]);

        $response = $this->from(route('login'))->post(route('checklogin'), [
            'email' => 'invalid_login@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }
}
