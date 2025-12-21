<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use App\Http\Controllers\CheckoutController;

class PaymentProcessController extends CheckoutController
{
public function process(Request $request)
    {
       // Check validation and stop if it fails
       $validationResult = $this->checkValidation($request);
       if ($validationResult !== true) {
           return $validationResult;
       }

        try {
            $payment_method = $request->payment_method;
            $is_pay_subscription = ($request->pay_subscription ?? "1") == "1";
            $isBuyNow = session('is_buy_now', false);

            // Get cart items and verify availability
            $cartItems = $this->getCartItems($isBuyNow);
            $this->verifyProductAvailability($cartItems);

            $total = $this->calculateTotal($cartItems);
            $address = $this->getCheckoutAddress();

            // Process payment based on method
            if ($payment_method === 'stripe') {
                return $this->processStripePayment($request, $address, $cartItems, $total, $is_pay_subscription);
            } else {
                return $this->processNonStripePayment($request, $address, $cartItems, $total, $payment_method, $isBuyNow);
            }

        } catch (\Exception $e) {
            Log::error('Checkout error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'There was an error processing your order: ' . $e->getMessage())
                ->withInput();
        }
    }

    private function getCartItems($isBuyNow)
    {
        session(['is_buy_now' => $isBuyNow]);

        if ($isBuyNow) {
            return session('buy_now_items', []);
        }

        if (Auth::check()) {
            $userCart = Cart::where('user_id', Auth::id())->with('items.product')->first();
            return $userCart ? $userCart->items->map(function($item) {
                return [
                    'id' => $item->product_id,
                    'name' => $item->product->name,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'image' => $item->product->image
                ];
            })->toArray() : [];
        }

        return session('cart', []);
    }

    private function verifyProductAvailability($cartItems)
    {
        foreach ($cartItems as $item) {
            $product = Product::find($item['id']);
            if (!$product || $product->stock < $item['quantity']) {
                throw new \Exception("{$item['name']} is no longer available in the requested quantity");
            }
        }
    }

    private function getCheckoutAddress()
    {
        $address = DB::table('addresses')
            ->where('id', session('checkout_address_id'))
            ->where('user_id', Auth::id())
            ->first();

        if (!$address) {
            throw new \Exception('Address not found');
        }

        return $address;
    }

    private function processStripePayment($request, $address, $cartItems, $total, $is_pay_subscription)
    {
        // Create order FIRST
        $order = $this->createOrder($request, $address, $cartItems, $total, 'stripe');
        $this->updateProductStock($cartItems);

        Stripe::setApiKey(config('services.stripe.secret'));

        // Build line items
        $lineItems = $this->buildStripeLineItems($cartItems, $is_pay_subscription);

        // Create Stripe session
        $mode = $is_pay_subscription ? 'subscription' : 'payment';
        $sessionOptions = [
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => $mode,
            'success_url' => route('checkout.success', ['order' => $order->order_number]),
            'cancel_url' => route('checkout.cancel'),
            'customer_email' => Auth::user()->email,
            'metadata' => [
                'order_number' => $order->order_number,
                'user_id' => Auth::id(),
                'subscription_interval_count' => $is_pay_subscription ? 3 : null,
            ],
        ];

        if ($is_pay_subscription) {
            $sessionOptions['payment_method_options'] = [
                'card' => ['request_three_d_secure' => 'automatic']
            ];
        }

        $session = StripeSession::create(
            $sessionOptions,
            ['idempotency_key' => 'order_' . $order->id]
        );

        $order->update(['stripe_session_id' => $session->id]);

        // Create payment record with pending status
        Payment::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'transaction_id' => $session->id, // Stripe session ID as initial transaction ID
            'payment_method' => 'stripe',
            'payment_status' => 'pending',
            'amount' => $total,
            'fee' => 0,
            'notes' => 'Stripe ' . ($is_pay_subscription ? 'subscription' : 'payment') . ' session created',
            'response_data' => json_encode([
                'session_id' => $session->id,
                'mode' => $mode,
                'url' => $session->url,
            ]),
        ]);

        return redirect($session->url);
    }

    private function buildStripeLineItems($cartItems, $is_pay_subscription)
    {
        $lineItems = [];

        foreach ($cartItems as $item) {
            if ($is_pay_subscription) {
                $intervalCount = 3;
                $perPeriodCents = (int) round(($item['price'] * 100) / $intervalCount);

                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => ['name' => $item['name']],
                        'unit_amount' => $perPeriodCents,
                        'recurring' => [
                            'interval' => 'month',
                            'interval_count' => $intervalCount,
                        ],
                    ],
                    'quantity' => $item['quantity'],
                ];
            } else {
                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => ['name' => $item['name']],
                        'unit_amount' => (int) ($item['price'] * 100),
                    ],
                    'quantity' => $item['quantity'],
                ];
            }
        }

        return $lineItems;
    }

    private function processNonStripePayment($request, $address, $cartItems, $total, $payment_method, $isBuyNow)
    {
        $order = $this->createOrder($request, $address, $cartItems, $total, $payment_method);
        $this->updateProductStock($cartItems);

        // Create payment record for cash/bkash
        Payment::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'transaction_id' => 'TXN-' . strtoupper(uniqid()), // Generate transaction ID
            'payment_method' => $payment_method,
            'payment_status' => $payment_method === 'cash' ? 'pending' : 'pending', // COD is pending until delivery
            'amount' => $total,
            'fee' => 0,
            'notes' => ucfirst($payment_method) . ' payment - awaiting confirmation',
        ]);

        $this->clearCartAndSession($isBuyNow);

        return redirect()->route('checkout.success', ['order' => $order->order_number])
            ->with('success', 'Order placed successfully!');
    }

    private function clearCartAndSession($isBuyNow)
    {
        if (!$isBuyNow) {
            if (Auth::check()) {
                $userCart = Cart::where('user_id', Auth::id())->first();
                if ($userCart) {
                    CartItem::where('cart_id', $userCart->id)->delete();
                }
            }
            session()->forget('cart');
        }

        session()->forget(['checkout_address_id', 'checkout_notes', 'is_buy_now', 'buy_now_items']);
    }

    private function checkValidation($request){
         if (!Auth::check()) {
            return redirect()->route('home')->with('error', 'Please login to checkout');
        }

        if (!session('checkout_address_id')) {
            return redirect()->route('checkout')->with('error', 'Please select a shipping address first');
        }

        // Validate payment method
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|string|in:cash,bkash,stripe',
        ]);

        // Log validation for debugging
        Log::info('Payment method validator check', [
            'payment_method' => $request->input('payment_method'),
            'fails' => $validator->fails(),
            'errors' => $validator->errors()->all(),
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        return true;
    }
}
