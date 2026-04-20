<?php
namespace App\Services;

use App\Repositories\PaymentProcessRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session as StripeSession;

class StripeSessionService
{
    public function __construct(
        protected PaymentProcessRepository $repo,
    ) {
    }

    /**
     * Build Stripe line items from cart items.
     *
     * @param array $cartItems
     * @param bool $isSubscription
     * @return array
     */
    public function buildLineItems(array $cartItems, bool $isSubscription): array
    {
        $lineItems = [];

        foreach ($cartItems as $item) {
            if ($isSubscription) {
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

    /**
     * Create a Stripe checkout session.
     *
     * @param \App\Models\Order $order
     * @param array $lineItems
     * @param array $options - Options including: stripeCustomerId, isSubscription, saveCardRequested, savedPaymentMethodId, total
     * @return \Stripe\Checkout\Session
     */
    public function createCheckoutSession($order, array $lineItems, array $options): StripeSession
    {
        $isSubscription = $options['isSubscription'] ?? false;
        $stripeCustomerId = $options['stripeCustomerId'];
        $saveCardRequested = $options['saveCardRequested'] ?? false;
        $savedPaymentMethodId = $options['savedPaymentMethodId'] ?? null;
        $total = $options['total'];

        $mode = $isSubscription ? 'subscription' : 'payment';
        $sessionOptions = [
            'payment_method_types' => ['card'],
            'line_items' => $lineItems,
            'mode' => $mode,
            'customer' => $stripeCustomerId,
            'success_url' => route('checkout.success', ['order' => $order->order_number]) . '&session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('checkout.cancel'),
            'metadata' => [
                'order_number' => $order->order_number,
                'user_id' => Auth::id(),
                'subscription_interval_count' => $isSubscription ? 3 : null,
            ],
        ];

        // Payment mode specific options
        if ($mode === 'payment') {
            $sessionOptions['saved_payment_method_options'] = [
                'payment_method_save' => $saveCardRequested ? 'enabled' : 'disabled',
            ];
        }

        // NOTE: Stripe Checkout Sessions have a limitation - you cannot pre-select a specific
        // saved payment method via the API. When you attach the customer, Stripe will
        // automatically display all their saved payment methods in the checkout UI,
        // but the user must manually select which one to use.
        if (!empty($savedPaymentMethodId) && $savedPaymentMethodId !== 'new') {
            Log::info('User wants to use saved payment method', [
                'payment_method_id' => $savedPaymentMethodId,
                'order_id' => $order->id,
                'customer_id' => $stripeCustomerId,
                'note' => 'Stripe Checkout will display all saved cards; user must select in Stripe UI'
            ]);
        }

        // Subscription specific options
        if ($isSubscription) {
            $sessionOptions['payment_method_options'] = [
                'card' => ['request_three_d_secure' => 'automatic']
            ];
        }

        // Save card for future use if requested
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
            'using_saved_card' => !empty($savedPaymentMethodId) && $savedPaymentMethodId !== 'new',
            'has_setup_future_usage' => isset($sessionOptions['payment_intent_data']['setup_future_usage']),
        ]);

        // Create session with idempotency key
        $session = StripeSession::create(
            $sessionOptions,
            ['idempotency_key' => 'order_' . $order->id]
        );

        // Update order with session ID
        $this->repo->updateOrderStripeSession($order, $session->id);

        // Create payment record with pending status
        $this->repo->createPayment([
            'order_id' => $order->id,
            'user_id' => Auth::id(),
            'transaction_id' => $session->id,
            'stripe_payment_intent_id' => null,
            'payment_method' => 'stripe',
            'payment_status' => 'pending',
            'amount' => $total,
            'fee' => 0,
            'notes' => 'Stripe ' . ($isSubscription ? 'subscription' : 'payment') . ' session created',
            'response_data' => json_encode([
                'session_id' => $session->id,
                'mode' => $mode,
                'url' => $session->url,
            ]),
        ]);

        return $session;
    }

    /**
     * Retrieve a Stripe session by ID (for future use).
     *
     * @param string $sessionId
     * @return \Stripe\Checkout\Session
     */
    public function retrieveSession(string $sessionId): StripeSession
    {
        return StripeSession::retrieve($sessionId);
    }

    /**
     * Retrieve a Stripe session with expanded payment intent data.
     *
     * @param string $sessionId
     * @return \Stripe\Checkout\Session
     */
    public function retrieveSessionWithPaymentIntent(string $sessionId): StripeSession
    {
        return StripeSession::retrieve([
            'id' => $sessionId,
            'expand' => ['payment_intent', 'invoice.payment_intent']
        ]);
    }

    /**
     * Extract payment intent ID from a Stripe session.
     *
     * @param \Stripe\Checkout\Session $session
     * @return string|null
     */
    public function extractPaymentIntentId(StripeSession $session): ?string
    {
        // Try direct payment_intent
        if (!empty($session->payment_intent)) {
            return is_string($session->payment_intent)
                ? $session->payment_intent
                : ($session->payment_intent->id ?? null);
        }

        // Try invoice.payment_intent (for subscriptions)
        if (!empty($session->invoice) && isset($session->invoice->payment_intent)) {
            return is_string($session->invoice->payment_intent)
                ? $session->invoice->payment_intent
                : ($session->invoice->payment_intent->id ?? null);
        }

        return null;
    }
}
