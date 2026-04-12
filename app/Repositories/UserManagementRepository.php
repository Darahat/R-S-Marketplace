<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class UserManagementRepository
{
    public function getPaginatedUsers(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        return User::query()
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%");
                });
            })
            ->when($filters['role'] ?? null, fn($q, $role) => $q->where('user_type', $role))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function countByRole(string $role): int
    {
        return User::where('user_type', $role)->count();
    }

    public function updateRole(User $user, string $newRole): bool
    {
        $user->user_type = $newRole;
        return $user->save();
    }
}
