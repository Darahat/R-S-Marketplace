<?php

namespace App\Repositories;

use App\Models\User;

class CustomerProfileRepository
{
    public function save(User $user): bool
    {
        return $user->save();
    }
}
