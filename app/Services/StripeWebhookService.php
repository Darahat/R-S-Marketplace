<?php

namespace App\Services;

use App\Repositories\StripeWebhookRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session as StripeSession;
use Stripe\PaymentIntent;
use Stripe\PaymentMethod as StripePaymentMethod;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeWebhookService
{
    public function __construct(private StripeWebhookRepository $repo)
    {
    }

    public function handle(Request $request): JsonResponse
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $endpointSecret = env('STRIPE_WEBHOOK_SECRET');

        // Early log to confirm the endpoint is actually hit
        Log::warning('Stripe webhook hit (pre-verify)', [
            'raw_length' => strlen($payload),
            'sig_header_present' => $sigHeader ? true : false,
        ]);

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);

            if ($event->type === 'checkout.session.completed') {
                $session = $event->data->object;

                // Log the entire session for debugging (use warning so it logs even if LOG_LEVEL=error)
                Log::warning('Stripe session object received', [
                    'session_id' => $session->id,
                    'payment_intent' => $session->payment_intent ?? 'NULL',
                    'payment_status' => $session->payment_status ?? 'NULL',
                    'mode' => $session->mode ?? 'NULL',
                    'all_keys' => array_keys((array) $session),
                ]);

                $orderNumber = $session->metadata->order_number;
                $order = $this->repo->findOrderByNumber($orderNumber);

                if ($order) {
                    // Get payment intent ID - may need to be fetched separately
                    $paymentIntentId = $session->payment_intent;

                    // If payment_intent is not directly available, try to retrieve it
                    if (!$paymentIntentId && isset($session->id)) {
                        try {
                            // Retrieve full session and expand nested relationships
                            $fullSession = StripeSession::retrieve([
                                'id' => $session->id,
                                'expand' => ['payment_intent', 'invoice.payment_intent'],
                            ]);
                            Log::warning('Retrieved full session from Stripe', [
                                'session_id' => $session->id,
                                'payment_intent_from_full' => isset($fullSession->payment_intent) ? (is_string($fullSession->payment_intent) ? $fullSession->payment_intent : ($fullSession->payment_intent->id ?? 'OBJ')) : 'NULL',
                                'subscription' => $fullSession->subscription ?? 'NULL',
                                'invoice' => isset($fullSession->invoice) ? (is_string($fullSession->invoice) ? $fullSession->invoice : ($fullSession->invoice->id ?? 'OBJ')) : 'NULL',
                            ]);

                            // Determine PI based on mode
                            if (!empty($fullSession->payment_intent)) {
                                $paymentIntentId = is_string($fullSession->payment_intent) ? $fullSession->payment_intent : ($fullSession->payment_intent->id ?? null);
                            } elseif (!empty($fullSession->invoice) && isset($fullSession->invoice->payment_intent)) {
                                $paymentIntentId = is_string($fullSession->invoice->payment_intent) ? $fullSession->invoice->payment_intent : ($fullSession->invoice->payment_intent->id ?? null);
                            }
                        } catch (\Exception $e) {
                            Log::error('Could not retrieve full session: ' . $e->getMessage());
                        }
                    }

                    // Update order status
                    $this->repo->markOrderPaid($order, $session->id, $paymentIntentId);

                    // Update or create payment record
                    $payment = $this->repo->findPaymentByOrderId($order->id);

                    if ($payment) {
                        $this->repo->updatePayment($payment, [
                            'payment_status' => 'paid',
                            'transaction_id' => $paymentIntentId ?? $session->id,
                            'stripe_payment_intent_id' => $paymentIntentId,
                            'paid_at' => now(),
                            'response_data' => json_encode([
                                'session_id' => $session->id,
                                'payment_intent' => $paymentIntentId,
                                'customer' => $session->customer ?? null,
                                'payment_status' => $session->payment_status ?? null,
                                'amount_total' => $session->amount_total / 100,
                            ]),
                        ]);
                    } else {
                        $this->repo->createPayment([
                            'order_id' => $order->id,
                            'user_id' => $order->user_id,
                            'transaction_id' => $paymentIntentId ?? $session->id,
                            'stripe_payment_intent_id' => $paymentIntentId,
                            'payment_method' => 'stripe',
                            'payment_status' => 'paid',
                            'amount' => $session->amount_total / 100,
                            'fee' => 0,
                            'paid_at' => now(),
                            'notes' => 'Payment completed via Stripe webhook',
                            'response_data' => json_encode([
                                'session_id' => $session->id,
                                'payment_intent' => $paymentIntentId,
                                'customer' => $session->customer ?? null,
                            ]),
                        ]);
                    }

                    // Save payment method if setup_intent is present or PaymentIntent has a payment_method
                    $this->savePaymentMethodIfPresent($paymentIntentId, $order->user_id);

                    Log::info('Stripe payment completed', [
                        'order_number' => $orderNumber,
                        'session_id' => $session->id,
                        'payment_intent_id' => $paymentIntentId,
                        'transaction_id' => $paymentIntentId ?? $session->id,
                    ]);
                }
            }

            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Stripe webhook error: ' . $e->getMessage(), [
                'raw_payload_sample' => substr($payload, 0, 500),
            ]);

            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }

    /**
     * Save payment method if setup_future_usage was enabled.
     */
    private function savePaymentMethodIfPresent(?string $paymentIntentId, ?int $userId): void
    {
        if (!$paymentIntentId || !$userId) {
            Log::warning('savePaymentMethodIfPresent: Missing parameters', [
                'payment_intent_id' => $paymentIntentId,
                'user_id' => $userId,
            ]);

            return;
        }

        try {
            // Retrieve the PaymentIntent to get the payment method
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);

            Log::info('PaymentIntent retrieved for saving method', [
                'payment_intent_id' => $paymentIntentId,
                'has_payment_method' => !empty($paymentIntent->payment_method),
                'payment_method_id' => $paymentIntent->payment_method ?? 'NULL',
                'setup_future_usage' => $paymentIntent->setup_future_usage ?? 'NULL',
            ]);

            if (!$paymentIntent->payment_method) {
                Log::warning('No payment method attached to PaymentIntent', [
                    'payment_intent_id' => $paymentIntentId,
                ]);

                return;
            }

            $paymentMethodId = $paymentIntent->payment_method;

            // Check if already saved
            $exists = $this->repo->paymentMethodExists($userId, $paymentMethodId);

            if ($exists) {
                Log::info('Payment method already saved', [
                    'user_id' => $userId,
                    'payment_method_id' => $paymentMethodId,
                ]);

                return;
            }

            // Retrieve payment method details from Stripe
            $stripePaymentMethod = StripePaymentMethod::retrieve($paymentMethodId);

            if ($stripePaymentMethod->type === 'card') {
                // Check if this is the first payment method for this user
                $isFirstMethod = $this->repo->countPaymentMethods($userId) === 0;

                $this->repo->createUserPaymentMethod([
                    'user_id' => $userId,
                    'stripe_payment_method_id' => $paymentMethodId,
                    'card_brand' => $stripePaymentMethod->card->brand,
                    'card_last4' => $stripePaymentMethod->card->last4,
                    'card_exp_month' => $stripePaymentMethod->card->exp_month,
                    'card_exp_year' => $stripePaymentMethod->card->exp_year,
                    'is_default' => $isFirstMethod,
                ]);

                Log::info('Payment method saved successfully', [
                    'user_id' => $userId,
                    'payment_method_id' => $paymentMethodId,
                    'brand' => $stripePaymentMethod->card->brand,
                    'last4' => $stripePaymentMethod->card->last4,
                ]);
            } else {
                Log::warning('Payment method type not supported', [
                    'type' => $stripePaymentMethod->type,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to save payment method: ' . $e->getMessage(), [
                'payment_intent_id' => $paymentIntentId,
                'user_id' => $userId,
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
