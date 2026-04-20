<?php
namespace App\Services;

use Illuminate\Support\Facades\Auth;
use App\Repositories\PaymentProcessRepository;

class PaymentProcessService
{
    public function __construct(
        protected CartService $cart_service,
        protected AddressService $address_service,
        protected OrderService $order_service,
        protected StockManagementService $stock_service,
        protected StripeCustomerService $stripe_customer_service,
        protected StripeSessionService $stripe_session_service,
        protected PaymentProcessRepository $repo,
    ) {
    }

    public function process(array $requestData)
    {

            $is_pay_subscription = ($requestData['pay_subscription'] ?? "1") == "1";
            $isBuyNow = session('is_buy_now', false);

            // Get cart items and verify availability
            $cartItems = $this->cart_service->getCheckoutCartItems($isBuyNow);
            $this->stock_service->validateAvailability($cartItems);

            $total = $this->cart_service->calculateTotal($cartItems);
            $address = $this->address_service->getAddressForCheckout((int) session('checkout_address_id'), (int) Auth::id());

            // Process payment based on method
            return [
                'total' => $total,
                'address' => $address,
                'cartItems' => $cartItems,
                'isBuyNow' => $isBuyNow,
                'is_pay_subscription' => $is_pay_subscription,
            ];
    }

    public function stripePaymentProcess($data, $address, $cartItems, $total, $is_pay_subscription)
    {
        // Create order
        $order = $this->order_service->createOrder(
            Auth::id(),
            [
                'address_id' => $address->id,
                'order_status' => 'to_pay',
                'total_amount' => $total,
                'payment_method' => 'stripe',
                'payment_status' => 'pending',
                'notes' => session('checkout_notes', ''),
            ],
            $cartItems
        );

        // Decrement stock
        $this->stock_service->decrementStock($cartItems);

        // Ensure Stripe customer exists
        $stripeCustomerId = $this->stripe_customer_service->ensureCustomerExists(Auth::user());

        // Build line items for Stripe
        $lineItems = $this->stripe_session_service->buildLineItems($cartItems, $is_pay_subscription);

        // Prepare session options
        $saveCardRequested = !empty($data['save_payment_card']) &&
                           (empty($data['saved_payment_method_id']) || $data['saved_payment_method_id'] === 'new');

        // Create Stripe checkout session
        $session = $this->stripe_session_service->createCheckoutSession($order, $lineItems, [
            'stripeCustomerId' => $stripeCustomerId,
            'isSubscription' => $is_pay_subscription,
            'saveCardRequested' => $saveCardRequested,
            'savedPaymentMethodId' => $data['saved_payment_method_id'] ?? null,
            'total' => $total,
        ]);

        return $session;
    }

 public function clearCartAndSession($isBuyNow)
    {
        $this->cart_service->clearCheckoutCart((bool) $isBuyNow);
    }
    public function paymentCreate($orderId, $payment_method, $total)
    {
        return $this->repo->createPayment([
            'order_id' => $orderId,
            'user_id' => Auth::id(),
            'transaction_id' => 'TXN-' . strtoupper(uniqid()), // Generate transaction ID
            'payment_method' => $payment_method,
            'payment_status' => $payment_method === 'cash' ? 'pending' : 'pending', // COD is pending until delivery
            'amount' => $total,
            'fee' => 0,
            'notes' => ucfirst($payment_method) . ' payment - awaiting confirmation',
        ]);
    }

    public function completePayment($order,$data):void{

        // Update order payment method and status
            $order->payment_method = $data['payment_method'];
            $order->order_status = 'confirmed';
            $order->payment_status = $data['payment_method'] === 'cash' ? 'pending' : 'pending';
            $this->repo->saveOrder($order);

            // Clear session
            session()->forget(['payment_order_id', 'payment_order_number']);
    }
}
