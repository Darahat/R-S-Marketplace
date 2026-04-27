<?php
namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Repositories\CheckoutRepository;
use Illuminate\Support\Collection;
use App\Services\CartService;

class CheckoutService{
    public function __construct(
        protected CheckoutRepository $repo,
        protected OrderService $order_service,
        protected CartService $cart_service,
        protected StockManagementService $stock_service,
        protected StripeCustomerService $stripe_customer_service,
        protected StripeSessionService $stripe_session_service,
    )
    {
     }

    public function index(){
        // Use Redis-backed cart service for both guest and authenticated users.
        $cartItems = $this->cart_service->getCartItems();

        $total = $this->calculateTotal($cartItems);

        if (count($cartItems) === 0) {
            return [
                'isEmptyCart' => true,
                'total' => 0,
                'cartItems' => [],
                'addresses' => collect(),
                'hasAddresses' => false,
                'defaultAddressId' => null,
            ];
        }

        $addresses = $this->getUserAddresses();
        $defaultAddress = $addresses->firstWhere('is_default', true) ?? $addresses->first();

            return [
                'isEmptyCart' => false,
                'total' => $total,
                'cartItems' => $cartItems,
                'addresses' => $addresses,
                'hasAddresses' => $addresses->isNotEmpty(),
                'defaultAddressId' => $defaultAddress?->id,
            ];
    }
    public function buyNow($product,$quantity){
        // Create single item array for buy now
        $cartItems = [[
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
            'quantity' => $quantity,
            'image' => $product->image
        ]];

        // Store buy now items in session for later use
        session(['buy_now_items' => $cartItems]);

        $total = $product->price * $quantity;


        $addresses = $this->getUserAddresses();
        $defaultAddress = $addresses->firstWhere('is_default', true) ?? $addresses->first();

            return [
                'cartItems' => $cartItems,
            'addresses' => $addresses,
            'total' => $total,
            'hasAddresses' => $addresses->isNotEmpty(),
            'defaultAddressId' => $defaultAddress?->id,
            ];
    }

    public function calculateTotal($cartItems)
    {
        return array_reduce($cartItems, function($carry, $item) {
            return $carry + ($item['price'] * $item['quantity']);
        }, 0);
    }

    public function review($data){
  // Verify address belongs to user
        $address = $this->repo->findUserAddress((int) $data['address_id'], (int) Auth::id());
             if (!$address) {
        return null;
    }
 session([
            'checkout_address_id' => $data['address_id'],
            'checkout_notes' => $data['notes'] ?? '',
            'is_buy_now' => (bool)($data['is_buy_now'] ?? false),
        ]);

        return $address;
    }
    public function getPaymentPageData(){
         // Check if this is a Buy Now checkout
        $isBuyNow = session('is_buy_now', false);

        $cartItems = $isBuyNow ? session('buy_now_items', []) :$this->getCartItems();

        if (count($cartItems) === 0) {
            return [
                'isEmptyCart' => true,
                'total' => 0,
                'subtotal' => 0,
                'shipping' => 0,
                'address' => null,
                'cartItems' => [],
                'savedPaymentMethods' => collect(),
                'data' => [
                    'title' => 'R&SMarketPlace | Payment'
                ],
            ];
        }

        $total = $this->calculateTotal($cartItems);
        $address = $this->getSelectedAddress();
        $savedPaymentMethods = $this->repo->getSavedPaymentMethods((int) Auth::id());
        return [
            'isEmptyCart' => false,
            'total' => $total,
            'subtotal' => $total,
            'shipping' => 0,
            'address' => $address,
            'cartItems' => $cartItems,
            'savedPaymentMethods' => $savedPaymentMethods,
            'data' => [
                'title' => 'R&SMarketPlace | Payment'
            ]
        ];
    }
    public function getSelectedAddress(): object
    {
    $address = $this->repo->findUserAddressWithRelations((int) session('checkout_address_id'), (int) Auth::id());

    if (!$address) {
        throw new \Exception('Address not found');
    }
    return $address;
    }

    public function getCartItems(){
        return $this->cart_service->getCartItems();
    }

    private function getUserAddresses(): Collection
    {
        return $this->repo->getUserAddresses((int) Auth::id());
    }
    /**
     * Handle payment success page - retrieve order and resolve Stripe payment intent if needed.
     */
    public function paymentSuccessData($data): Order
    {
        // Get order
        $orderNumber = $data['order'];
        $order = $this->order_service->findUserOrderByNumber($orderNumber, (int) Auth::id());

        if (!$order) {
            throw new \Exception('Order not found');
        }

        // Optional fallback: if we have session_id and no PI yet, try to resolve it now
        try {
            $sessionId = $data['session_id'] ?? null;
            if ($sessionId && empty($order->stripe_payment_intent_id)) {
                $fullSession = $this->stripe_session_service->retrieveSessionWithPaymentIntent($sessionId);
                $paymentIntentId = $this->stripe_session_service->extractPaymentIntentId($fullSession);

                if ($paymentIntentId) {
                    $order->stripe_session_id = $sessionId;
                    $order->stripe_payment_intent_id = $paymentIntentId;
                    $order->payment_status = $order->payment_status === 'paid' ? $order->payment_status : 'paid';
                    $this->repo->saveOrder($order);

                    // Fallback: save payment method if webhook hasn't done it yet
                    $this->stripe_customer_service->savePaymentMethodFromIntent($paymentIntentId, $order->user_id);
                }
            }
            return $order;
        } catch (\Exception $e) {
            Log::warning('Checkout success page PI resolve failed: ' . $e->getMessage());
            return $order;
        }
    }


}
