<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\UserManagementRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AdminUserManagementService
{
    public function __construct(private UserManagementRepository $repo)
    {
    }

    public function getUsers(array $filters): LengthAwarePaginator
    {
        return $this->repo->getPaginatedUsers($filters);
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
            $adminCount = $this->repo->countByRole(User::ADMIN);
            if ($adminCount <= 1) {
                return [
                    'success' => false,
                    'message' => 'At least one admin user must remain in the system.',
                ];
            }
        }

        $oldRole = $target->user_type;
        $this->repo->updateRole($target, $newRole);

        return [
            'success' => true,
            'oldRole' => $oldRole,
            'newRole' => $target->user_type,
            'message' => "User role updated from '{$oldRole}' to '{$target->user_type}'.",
        ];
    }
}
