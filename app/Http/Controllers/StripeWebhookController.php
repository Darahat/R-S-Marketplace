<?php
namespace App\Http\Controllers;
use App\Models\Order;
use App\Models\Payment;
use App\Models\UserPaymentMethod;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Webhook;
use Stripe\PaymentMethod as StripePaymentMethod;
use Illuminate\Http\Request;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
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
                    'all_keys' => array_keys((array)$session),
                ]);

                $orderNumber = $session->metadata->order_number;
                $order = Order::where('order_number', $orderNumber)->first();

                if($order){
                    // Get payment intent ID - may need to be fetched separately
                    $paymentIntentId = $session->payment_intent;

                    // If payment_intent is not directly available, try to retrieve it
                    if (!$paymentIntentId && isset($session->id)) {
                        try {
                            // Retrieve full session and expand nested relationships
                            $fullSession = \Stripe\Checkout\Session::retrieve([
                                'id' => $session->id,
                                'expand' => ['payment_intent', 'invoice.payment_intent']
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
                    $order->payment_status = 'paid';
                    $order->order_status = 'Processing';
                    $order->stripe_session_id = $session->id;
                    $order->stripe_payment_intent_id = $paymentIntentId;
                    $order->save();

                    // Update or create payment record
                    $payment = Payment::where('order_id', $order->id)->first();

                    if ($payment) {
                        $payment->update([
                            'payment_status' => 'paid',  // Changed from 'completed' to match enum
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
                        Payment::create([
                            'order_id' => $order->id,
                            'user_id' => $order->user_id,
                            'transaction_id' => $paymentIntentId ?? $session->id,
                            'stripe_payment_intent_id' => $paymentIntentId,
                            'payment_method' => 'stripe',
                            'payment_status' => 'paid',  // Changed from 'completed' to match enum
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
     * Save payment method if setup_future_usage was enabled
     */
    private function savePaymentMethodIfPresent($paymentIntentId, $userId)
    {
        if (!$paymentIntentId || !$userId) {
            Log::warning('savePaymentMethodIfPresent: Missing parameters', [
                'payment_intent_id' => $paymentIntentId,
                'user_id' => $userId
            ]);
            return;
        }

        try {
            // Retrieve the PaymentIntent to get the payment method
            $paymentIntent = \Stripe\PaymentIntent::retrieve($paymentIntentId);

            Log::info('PaymentIntent retrieved for saving method', [
                'payment_intent_id' => $paymentIntentId,
                'has_payment_method' => !empty($paymentIntent->payment_method),
                'payment_method_id' => $paymentIntent->payment_method ?? 'NULL',
                'setup_future_usage' => $paymentIntent->setup_future_usage ?? 'NULL',
            ]);

            if (!$paymentIntent->payment_method) {
                Log::warning('No payment method attached to PaymentIntent', [
                    'payment_intent_id' => $paymentIntentId
                ]);
                return;
            }

            $paymentMethodId = $paymentIntent->payment_method;

            // Check if already saved
            $exists = UserPaymentMethod::where('user_id', $userId)
                ->where('stripe_payment_method_id', $paymentMethodId)
                ->exists();

            if ($exists) {
                Log::info('Payment method already saved', [
                    'user_id' => $userId,
                    'payment_method_id' => $paymentMethodId
                ]);
                return; // Already saved
            }

            // Retrieve payment method details from Stripe
            $stripePaymentMethod = StripePaymentMethod::retrieve($paymentMethodId);

            if ($stripePaymentMethod->type === 'card') {
                // Check if this is the first payment method for this user
                $isFirstMethod = UserPaymentMethod::where('user_id', $userId)->count() === 0;

                UserPaymentMethod::create([
                    'user_id' => $userId,
                    'stripe_payment_method_id' => $paymentMethodId,
                    'card_brand' => $stripePaymentMethod->card->brand,
                    'card_last4' => $stripePaymentMethod->card->last4,
                    'card_exp_month' => $stripePaymentMethod->card->exp_month,
                    'card_exp_year' => $stripePaymentMethod->card->exp_year,
                    'is_default' => $isFirstMethod, // First card becomes default
                ]);

                Log::info('Payment method saved successfully', [
                    'user_id' => $userId,
                    'payment_method_id' => $paymentMethodId,
                    'brand' => $stripePaymentMethod->card->brand,
                    'last4' => $stripePaymentMethod->card->last4,
                ]);
            } else {
                Log::warning('Payment method type not supported', [
                    'type' => $stripePaymentMethod->type
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to save payment method: ' . $e->getMessage(), [
                'payment_intent_id' => $paymentIntentId,
                'user_id' => $userId,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}