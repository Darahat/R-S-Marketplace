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

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);

            if ($event->type === 'checkout.session.completed') {
                $session = $event->data->object;

                $orderNumber = $session->metadata->order_number;
                $order = Order::where('order_number', $orderNumber)->first();

                if($order){
                    // Update order status
                    $order->payment_status = 'paid';
                    $order->order_status = 'Processing';
                    $order->stripe_payment_intent_id = $session->payment_intent ?? null;
                    $order->save();

                    // Update or create payment record
                    $payment = Payment::where('order_id', $order->id)->first();

                    if ($payment) {
                        $payment->update([
                            'payment_status' => 'completed',
                            'transaction_id' => $session->payment_intent ?? $session->id,
                            'paid_at' => now(),
                            'response_data' => json_encode([
                                'session_id' => $session->id,
                                'payment_intent' => $session->payment_intent,
                                'customer' => $session->customer,
                                'payment_status' => $session->payment_status,
                                'amount_total' => $session->amount_total / 100,
                            ]),
                        ]);
                    } else {
                        Payment::create([
                            'order_id' => $order->id,
                            'user_id' => $order->user_id,
                            'transaction_id' => $session->payment_intent ?? $session->id,
                            'payment_method' => 'stripe',
                            'payment_status' => 'completed',
                            'amount' => $session->amount_total / 100,
                            'fee' => 0,
                            'paid_at' => now(),
                            'notes' => 'Payment completed via Stripe webhook',
                            'response_data' => json_encode([
                                'session_id' => $session->id,
                                'payment_intent' => $session->payment_intent,
                                'customer' => $session->customer,
                            ]),
                        ]);
                    }

                    Log::info('Stripe payment completed', [
                        'order_number' => $orderNumber,
                        'transaction_id' => $session->payment_intent ?? $session->id,
                    ]);
                }
            }
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            Log::error('Stripe webhook error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }
}