<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdminUserManagementService
{
    public function getUsers(array $filters): LengthAwarePaginator
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
            ->paginate(20)
            ->withQueryString();
    }

    public function updateUserRole(User $actor, User $target, string $newRole): array
    {
        if ($actor->id === $target->id && $newRole !== User::ADMIN) {
            return [
                'success' => false,
                'message' => 'You cannot remove your own admin access.',
            ];
        }

        if ($target->user_type === User::ADMIN && $newRole !== User::ADMIN) {
            $adminCount = User::where('user_type', User::ADMIN)->count();
            if ($adminCount <= 1) {
                return [
                    'success' => false,
                    'message' => 'At least one admin user must remain in the system.',
                ];
            }
        }

        $oldRole = $target->user_type;
        $target->user_type = $newRole;
        $target->save();

        return [
            'success' => true,
            'oldRole' => $oldRole,
            'newRole' => $target->user_type,
            'message' => "User role updated from '{$oldRole}' to '{$target->user_type}'.",
        ];
    }
}
