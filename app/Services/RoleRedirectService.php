<?php

namespace App\Services;

use App\Models\User;

class RoleRedirectService
{
    public function redirectByRole(User $user): string
    {
        return match ($user->user_type) {
            User::ADMIN => route('admin.dashboard'),
            User::CUSTOMER => route('home'),
            default => route('home'),
        };
    }
}
