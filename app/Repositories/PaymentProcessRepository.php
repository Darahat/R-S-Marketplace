<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;

class PaymentProcessRepository
{
    public function updateOrderStripeSession(Order $order, string $sessionId): bool
    {
        return $order->update(['stripe_session_id' => $sessionId]);
    }

    public function createPayment(array $data): Payment
    {
        return Payment::create($data);
    }

    public function saveUserStripeCustomerId(User $user, string $stripeCustomerId): bool
    {
        $user->stripe_customer_id = $stripeCustomerId;
        return $user->save();
    }

    public function saveOrder(Order $order): bool
    {
        return $order->save();
    }
}
