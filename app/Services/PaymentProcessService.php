<?php
namespace App\Services;

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
use App\Models\UserPaymentMethod;
use App\Repositories\CheckoutRepository;
use Stripe\Stripe;
use Stripe\Customer as StripeCustomer;
use App\Models\Address;
use Illuminate\Support\Collection;
use Stripe\Checkout\Session as StripeSession;
use App\Models\Payment;
class PaymentProcessService{
     public function __construct(protected CheckoutService $checkout_service)
    {
     }

    public function index(){}

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
        public function process(array $requestData){

            $is_pay_subscription = ($requestData['pay_subscription'] ?? "1") == "1";
            $isBuyNow = session('is_buy_now', false);

            // Get cart items and verify availability
            $cartItems = $this->getCartItems($isBuyNow);
            $this->verifyProductAvailability($cartItems);

            $total = $this->checkout_service->calculateTotal($cartItems);
            $address = $this->getCheckoutAddress();

            // Process payment based on method
            return [
            'total' => $total,
            'address' => $address,
            'cartItems' => $cartItems,
            'isBuyNow' => $isBuyNow,
            'is_pay_subscription' => $is_pay_subscription,
            ];
    }

    public function stripePaymentProcess($data, $address, $cartItems, $total, $is_pay_subscription){

            $order = $this->checkout_service->createOrderData($address, $total, 'stripe', $cartItems);


        $this->checkout_service->updateProductStock($cartItems);

        Stripe::setApiKey(config('services.stripe.secret'));

        // Ensure Stripe customer exists for the user
        $stripeCustomerId = $this->ensureStripeCustomer(Auth::user());

        // Build line items
        $lineItems = $this->buildStripeLineItems($cartItems, $is_pay_subscription);
        $isUsingSavedCard = !empty($data['saved_payment_method_id']) && $data['saved_payment_method_id'] !== 'new';
        $saveCardRequested = !empty($data['save_payment_card']) && !$isUsingSavedCard;

        // Create Stripe session
        $mode = $is_pay_subscription ? 'subscription' : 'payment';
        $sessionOptions = [
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => $mode,
            'customer' => $stripeCustomerId, // Attach customer to session
            // Include session id placeholder so we can retrieve PI on success as a fallback
            'success_url' => route('checkout.success', ['order' => $order->order_number]) . '&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('checkout.cancel'),
             'metadata' => [
                'order_number' => $order->order_number,
                'user_id' => Auth::id(),
                'subscription_interval_count' => $is_pay_subscription ? 3 : null,
            ],
        ];

        if ($mode === 'payment') {
            $sessionOptions['saved_payment_method_options'] = [
                'payment_method_save' => $saveCardRequested ? 'enabled' : 'disabled',
            ];
        }

        // NOTE: Stripe Checkout Sessions have a limitation - you cannot pre-select a specific
        // saved payment method via the API. When you attach the customer, Stripe will
        // automatically display all their saved payment methods in the checkout UI,
        // but the user must manually select which one to use.

        // Log if user selected a saved payment method (for tracking purposes)
        if (!empty($data['saved_payment_method_id']) && $data['saved_payment_method_id'] !== 'new') {
            Log::info('User wants to use saved payment method', [
                'payment_method_id' => $data['saved_payment_method_id'],
                'order_id' => $order->id,
                'customer_id' => $stripeCustomerId,
                'note' => 'Stripe Checkout will display all saved cards; user must select in Stripe UI'
            ]);
        }

        if ($is_pay_subscription) {
            $sessionOptions['payment_method_options'] = [
                'card' => ['request_three_d_secure' => 'automatic']
            ];
        }

        // Save card for future use if checkbox was checked and not using a saved card.
        // Note: Payment method will be automatically saved via webhook after successful payment
        // See StripeWebhookController::savePaymentMethodIfPresent()
        if ($mode === 'payment' && $saveCardRequested) {
            if (!isset($sessionOptions['payment_intent_data'])) {
                $sessionOptions['payment_intent_data'] = [];
            }
            $sessionOptions['payment_intent_data']['setup_future_usage'] = 'off_session';
        }

        Log::info('Stripe checkout session options prepared', [
            'order_id' => $order->id,
            'customer_id' => $stripeCustomerId,
            'mode' => $mode,
            'save_card_requested' => $saveCardRequested,
            'using_saved_card' => $isUsingSavedCard,
            'has_setup_future_usage' => isset($sessionOptions['payment_intent_data']['setup_future_usage']),
        ]);
            $session = StripeSession::create(
            $sessionOptions,
            ['idempotency_key' => 'order_' . $order->id]
        );
            // dd($session); // Commented out to prevent halting flow

        $order->update(['stripe_session_id' => $session->id]);

        // Create payment record with pending status
        Payment::create([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'transaction_id' => $session->id, // Stripe session ID as initial transaction ID
            'stripe_payment_intent_id' => null,
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
        return $session;
    }


    /**
     * Ensure a Stripe customer exists for the given user and return its ID.
     */
    public function ensureStripeCustomer($user)
    {
        if (!$user) {
            throw new \Exception('User not authenticated');
        }
        if (!empty($user->stripe_customer_id)) {
            return $user->stripe_customer_id;
        }
        $customer = StripeCustomer::create([
            'email' => $user->email,
            'name' => $user->name,
            'metadata' => [
                'app_user_id' => $user->id,
            ],
        ]);
        $user->stripe_customer_id = $customer->id;
        $user->save();
        return $customer->id;
    }

    public function buildStripeLineItems($cartItems, $is_pay_subscription)
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
 public function clearCartAndSession($isBuyNow)
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
}
