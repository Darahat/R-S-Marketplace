<?php

namespace Tests\Unit;

use App\Models\User;
use App\Repositories\AuthRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected AuthRepository $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->repository = new AuthRepository();
    }

    public function test_create_customer_creates_customer_user(): void
    {
        $user = $this->repository->createCustomer([
            'name' => 'Repo Customer',
            'email' => 'repo_customer@example.com',
            'password' => 'password123',
            'mobile' => '01711111111',
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame(User::CUSTOMER, $user->user_type);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'email' => 'repo_customer@example.com',
            'user_type' => User::CUSTOMER,
        ]);
    }

    public function test_find_user_by_id_returns_user_when_found(): void
    {
        $user = User::factory()->create();

        $found = $this->repository->findUserById($user->id);

        $this->assertNotNull($found);
        $this->assertSame($user->id, $found->id);
    }

    public function test_find_user_by_id_returns_null_when_not_found(): void
    {
        $found = $this->repository->findUserById(999999);

        $this->assertNull($found);
    }

    public function test_update_login_metadata_updates_user_login_fields(): void
    {
        $user = User::factory()->create([
            'last_ip' => null,
            'last_device' => null,
            'last_login' => null,
        ]);

        $updated = $this->repository->updateLoginMetaData($user, '127.0.0.1', 'Google Chrome on macOS');

        $this->assertTrue($updated);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'last_ip' => '127.0.0.1',
            'last_device' => 'Google Chrome on macOS',
        ]);
    }
}
