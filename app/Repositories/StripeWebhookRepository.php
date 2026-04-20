<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\Payment;
use App\Models\UserPaymentMethod;

class StripeWebhookRepository
{
    public function findOrderByNumber(string $orderNumber): ?Order
    {
        return Order::where('order_number', $orderNumber)->first();
    }

    public function markOrderPaid(Order $order, string $sessionId, ?string $paymentIntentId): bool
    {
        return $order->update([
            'payment_status' => 'paid',
            'order_status' => 'Processing',
            'stripe_session_id' => $sessionId,
            'stripe_payment_intent_id' => $paymentIntentId,
        ]);
    }

    public function findPaymentByOrderId(int $orderId): ?Payment
    {
        return Payment::where('order_id', $orderId)->first();
    }

    public function updatePayment(Payment $payment, array $data): bool
    {
        return $payment->update($data);
    }

    public function createPayment(array $data): Payment
    {
        return Payment::create($data);
    }

    public function paymentMethodExists(int $userId, string $paymentMethodId): bool
    {
        return UserPaymentMethod::where('user_id', $userId)
            ->where('stripe_payment_method_id', $paymentMethodId)
            ->exists();
    }

    public function countPaymentMethods(int $userId): int
    {
        return UserPaymentMethod::where('user_id', $userId)->count();
    }

    public function createUserPaymentMethod(array $data): UserPaymentMethod
    {
        return UserPaymentMethod::create($data);
    }
}
