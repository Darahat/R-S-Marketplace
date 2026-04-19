<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminUserManagementJourneyTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_can_view_users_index(): void
    {
        $admin = User::factory()->create([
            'user_type' => User::ADMIN,
            'mobile' => '01610000101',
        ]);

        $user = User::factory()->create([
            'user_type' => User::CUSTOMER,
            'mobile' => '01610000102',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.users.index'));

        $response->assertStatus(200);
        $response->assertViewIs('backend_panel_view_admin.pages.users.index');
        $response->assertSee($user->email);
    }

    public function test_admin_can_view_single_user_details(): void
    {
        $admin = User::factory()->create([
            'user_type' => User::ADMIN,
            'mobile' => '01610000103',
        ]);

        $target = User::factory()->create([
            'user_type' => User::CUSTOMER,
            'mobile' => '01610000104',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.users.show', $target->id));

        $response->assertStatus(200);
        $response->assertViewIs('backend_panel_view_admin.pages.users.show');
        $response->assertSee($target->email);
    }

    public function test_admin_can_update_user_role(): void
    {
        $admin = User::factory()->create([
            'user_type' => User::ADMIN,
            'mobile' => '01610000105',
        ]);

        // Ensure there is more than one admin so safeguard does not block role updates in this test.
        User::factory()->create([
            'user_type' => User::ADMIN,
            'mobile' => '01610000106',
        ]);

        $target = User::factory()->create([
            'user_type' => User::CUSTOMER,
            'mobile' => '01610000107',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.users.update-role', $target->id), [
            'user_type' => User::ADMIN,
        ]);

        $response->assertRedirect(route('admin.users.show', $target->id));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $target->id,
            'user_type' => User::ADMIN,
        ]);
    }

    public function test_customer_cannot_access_admin_user_routes(): void
    {
        $customer = User::factory()->create([
            'user_type' => User::CUSTOMER,
            'mobile' => '01610000108',
        ]);

        $response = $this->actingAs($customer)->get(route('admin.users.index'));

        $response->assertStatus(403);
    }
}
