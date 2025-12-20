<?php
namespace App\Http\Controllers;
use App\Models\Order;

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
                $order = Order::find($orderNumber);
                if($order){
                    $order->payment_status = 'paid';
                    $order->order_status = 'Processing';
                    $order->save();
                }
            }
            return response()->json(['status' => 'success']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }
}
