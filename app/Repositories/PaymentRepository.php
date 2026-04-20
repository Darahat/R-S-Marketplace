<?php

namespace App\Repositories;

use App\Models\Payment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PaymentRepository
{
    public function getFilteredPayments(array $filters): LengthAwarePaginator
    {
        return Payment::with(['order', 'user'])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where('transaction_id', 'like', '%' . $search . '%')
                        ->orWhereHas('order', function ($orderQuery) use ($search) {
                            $orderQuery->where('order_number', 'like', '%' . $search . '%');
                        })
                        ->orWhereHas('user', function ($userQuery) use ($search) {
                            $userQuery->where('name', 'like', '%' . $search . '%')
                                ->orWhere('email', 'like', '%' . $search . '%');
                        });
                });
            })
            ->when($filters['payment_status'] ?? null, fn ($query, $status) => $query->where('payment_status', $status))
            ->when($filters['payment_method'] ?? null, fn ($query, $method) => $query->where('payment_method', $method))
            ->when($filters['date_from'] ?? null, fn ($query, $from) => $query->whereDate('created_at', '>=', $from))
            ->when($filters['date_to'] ?? null, fn ($query, $to) => $query->whereDate('created_at', '<=', $to))
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    }

    public function findOrFail(int $id): Payment
    {
        return Payment::findOrFail($id);
    }

    public function findDetailedByIdOrFail(int $id): Payment
    {
        return Payment::with(['order', 'user'])->findOrFail($id);
    }

    public function save(Payment $payment): bool
    {
        return $payment->save();
    }

    public function update(Payment $payment, array $data): bool
    {
        return $payment->update($data);
    }

    public function getStatistics(string $today): array
    {
        return [
            'total_payments' => Payment::count(),
            'completed_payments' => Payment::where('payment_status', 'completed')->count(),
            'pending_payments' => Payment::where('payment_status', 'pending')->count(),
            'failed_payments' => Payment::where('payment_status', 'failed')->count(),
            'refunded_payments' => Payment::where('payment_status', 'refunded')->count(),
            'total_amount' => Payment::sum('amount'),
            'completed_amount' => Payment::where('payment_status', 'completed')->sum('amount'),
            'pending_amount' => Payment::where('payment_status', 'pending')->sum('amount'),
            'today_amount' => Payment::whereDate('created_at', $today)->sum('amount'),
            'month_amount' => Payment::whereYear('created_at', date('Y'))
                ->whereMonth('created_at', date('m'))
                ->sum('amount'),
        ];
    }

    public function sumCompletedForMonth(int $year, int $month): float
    {
        return (float) Payment::where('payment_status', 'completed')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->sum('amount');
    }

    public function sumFailedForMonth(int $year, int $month): float
    {
        return (float) Payment::where('payment_status', 'failed')
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->sum('amount');
    }

    public function countByMethod(string $method): int
    {
        return Payment::where('payment_method', $method)->count();
    }

    public function sumByMethod(string $method): float
    {
        return (float) Payment::where('payment_method', $method)->sum('amount');
    }
}
