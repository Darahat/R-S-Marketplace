<?php
namespace App\Http\Controllers;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Stripe\Stripe;
use Stripe\Webhook;
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
                            'payment_status' => 'completed',
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
                            'payment_status' => 'completed',
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
}
