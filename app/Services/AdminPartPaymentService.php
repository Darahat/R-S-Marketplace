<?php
namespace App\Services;

use App\Repositories\PaymentRepository;
use Carbon\Carbon;
use Stripe\Refund;

class AdminPartPaymentService
{
    public function __construct(private PaymentRepository $repo)
    {
    }

    public function getPaymentsService(array $filters)
    {
        return $this->repo->getFilteredPayments($filters);
    }

    public function getPaymentDetailsService(int $id)
    {
        return $this->repo->findDetailedByIdOrFail($id);
    }

    public function markFailedService(array $validator, int $id): string
    {
        $payment = $this->repo->findOrFail($id);
        $payment->payment_status = 'failed';

        if (!empty($validator['notes'])) {
            $payment->notes = $validator['notes'];
        }

        $this->repo->save($payment);

        return $payment->payment_status;
    }

    public function processService(int $id): string
    {
        $payment = $this->repo->findOrFail($id);

        // Pull the related order first to sync identifiers
        $order = $payment->order;

        // Mark payment completed and sync Stripe PaymentIntent ID from order if available
        $payment->payment_status = 'completed';
        $payment->paid_at = now();
        if ($order && empty($payment->stripe_payment_intent_id) && !empty($order->stripe_payment_intent_id)) {
            $payment->stripe_payment_intent_id = $order->stripe_payment_intent_id;
        }
        $this->repo->save($payment);

        // Update order payment status
        if ($order) {
            $order->payment_status = 'paid';
            $order->save();
        }

        return $order->payment_status;
    }

    public function refundService(array $validator, int $id): array
    {
        $payment = $this->repo->findOrFail($id);

        $order = $payment->order;

        if ($payment->payment_status !== 'completed') {
            return [
                'success' => false,
                'message' => 'Only completed payments can be refunded!'
            ];
        }

        if ($validator['refund_amount'] > $payment->amount) {
            return [
                'success' => false,
                'message' => 'Refund amount cannot be greater than payment amount!'
            ];
        }

        try {
            $refund = Refund::create([
                'payment_intent' => $payment->stripe_payment_intent_id,
                'amount' => (int) ($validator['refund_amount'] * 100),
            ]);

            // Save refund id and mark status pending until finalized
            $this->repo->update($payment, [
                'payment_status' => 'refund_pending',
                'stripe_refund_id' => $refund->id ?? null,
                'notes' => $validator['notes'] ?? null,
            ]);

            // Update order payment status if fully refunded
            if ($validator['refund_amount'] == $payment->amount && $payment->order) {
                if ($order) {
                    $order->payment_status = 'unpaid';
                    $order->save();
                }
            }

            return [
                'success' => true,
                'message' => 'Payment refunded successfully',
                'payment_status' => $payment->fresh()->payment_status,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Stripe refund failed: ' . $e->getMessage()
            ];
        }
    }

    public function updateNotesService(array $validator, int $id): array
    {
        $payment = $this->repo->findOrFail($id);
        $payment->notes = $validator['notes'] ?? null;
        $this->repo->save($payment);

        return [
            'success' => true,
            'message' => 'Payment notes updated successfully',
        ];
    }

    public function getStatisticsService(): array
    {
        $today = Carbon::now()->format('Y-m-d');

        return $this->repo->getStatistics($today);
    }

    public function getTrendsService(): array
    {
        $trends = [];

        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);

            $trends[] = [
                'month' => $date->format('M Y'),
                'completed' => $this->repo->sumCompletedForMonth($date->year, $date->month),
                'failed' => $this->repo->sumFailedForMonth($date->year, $date->month),
            ];
        }

        return $trends;
    }

    public function getMethodBreakdownService(): array
    {
        $methods = ['cod', 'card', 'bkash', 'stripe'];
        $breakdown = [];

        foreach ($methods as $method) {
            $breakdown[] = [
                'method' => strtoupper($method),
                'count' => $this->repo->countByMethod($method),
                'amount' => $this->repo->sumByMethod($method),
            ];
        }

        return $breakdown;
    }
}
