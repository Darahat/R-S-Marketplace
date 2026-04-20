<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthRepository
{
    public function createCustomer(array $data): User
    {
        return User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'mobile' => $data['mobile'],
            'user_type' => User::CUSTOMER,
        ]);
    }

    public function findUserById(int $userId): ?User
    {
        return User::find($userId);
    }

    public function findCustomerByEmail(string $email): ?User
    {
        return User::where('email', $email)
            ->where('user_type', User::CUSTOMER)
            ->first();
    }

    public function updatePassword(User $user, string $password, string $rememberToken): bool
    {
        return $user->update([
            'password' => Hash::make($password),
            'remember_token' => $rememberToken,
        ]);
    }

    public function updateLoginMetaData(User $user, string $ip, string $device): bool
    {
        return $user->update([
            'last_login' => now(),
            'last_ip' => $ip,
            'last_device' => $device,
        ]);
    }
}
