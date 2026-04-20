<?php

namespace App\Repositories;

use App\Models\LoginAudit;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class LoginAuditRepository
{
    public function createAttempt(array $data): LoginAudit
    {
        return LoginAudit::create($data);
    }

    public function getPaginated(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        return LoginAudit::query()
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('email', 'like', '%' . $search . '%')
                        ->orWhere('ip', 'like', '%' . $search . '%')
                        ->orWhere('reason', 'like', '%' . $search . '%');
                });
            })
            ->when($filters['login_type'] ?? null, fn ($q, $v) => $q->where('login_type', $v))
            ->when($filters['status'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($filters['date_from'] ?? null, fn ($q, $v) => $q->whereDate('attempted_at', '>=', $v))
            ->when($filters['date_to'] ?? null, fn ($q, $v) => $q->whereDate('attempted_at', '<=', $v))
            ->latest('attempted_at')
            ->paginate($perPage);
    }
}
