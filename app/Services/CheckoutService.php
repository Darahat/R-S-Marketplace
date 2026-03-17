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
use App\Models\Address;
use Illuminate\Support\Collection;
use Stripe\Checkout\Session as StripeSession;
class CheckoutService{
     public function __construct()
    {
     }

    public function index(){
         // Get cart items from database or session
        if (Auth::check()) {
            $userCart = Cart::where('user_id', Auth::id())->with('items.product')->first();
            $cartItems = $userCart ? $userCart->items->map(function($item) {
                return [
                    'id' => $item->product_id,
                    'name' => $item->product->name,
                    'price' => $item->price,
                    'quantity' => $item->quantity,
                    'image' => $item->product->image
                ];
            })->toArray() : [];
        } else {
            $cartItems = session('cart', []);
        }

        $total = $this->calculateTotal($cartItems);


        // if (count($cartItems) === 0) {
        //     return redirect()->route('cart.view')->with('error', 'Your cart is empty');
        // }

        $addresses = $this->getUserAddresses();
        $defaultAddress = $addresses->firstWhere('is_default', true) ?? $addresses->first();

            return [
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
        $address = DB::table('addresses')
            ->where('id', $data['address_id'])
            ->where('user_id', Auth::id())
            ->first();
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


        // if (count($cartItems) === 0) {
        //     return redirect()->route('cart.view')->with('error', 'Your cart is empty');
        // }

        $total = $this->calculateTotal($cartItems);
        $address = $this->getSelectedAddress();
        $savedPaymentMethods = UserPaymentMethod::where('user_id', Auth::id())
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
        return [
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
    $address = Address::with(['district', 'upazila', 'union'])
        ->where('id', session('checkout_address_id'))
        ->where('user_id', Auth::id())
        ->first();

    if (!$address) {
        throw new \Exception('Address not found');
    }
    return $address;
    }

    public function getCartItems(){
    $userCart = Cart::where('user_id', Auth::id())->with('items.product')->first();
                    return $userCart ? $userCart->items->map(function ($item) {
                    return [
                        'id' => $item->product_id,
                        'name' => $item->product->name,
                        'price' => $item->price,
                        'quantity' => $item->quantity,
                        'image' => $item->product->image
                    ];
                })->toArray() : [];
    }

    private function getUserAddresses(): Collection
    {
        return DB::table('addresses')
            ->leftJoin('districts', 'addresses.district_id', '=', 'districts.id')
            ->leftJoin('upazilas', 'addresses.upazila_id', '=', 'upazilas.id')
            ->leftJoin('unions', 'addresses.union_id', '=', 'unions.id')
            ->where('addresses.user_id', Auth::id())
            ->select(
                'addresses.*',
                'districts.name as district_name',
                'upazilas.name as upazila_name',
                'unions.name as union_name'
            )
            ->orderByDesc('addresses.is_default')
            ->orderByDesc('addresses.id')
            ->get();
    }

     public function updateProductStock($cartItems)
    {
        foreach ($cartItems as $item) {
            $product = Product::find($item['id']);
            if ($product) {
                $product->stock -= $item['quantity'];
                $product->save();
            }
        }
    }
    public function createOrderData($address, $total, $paymentMethod,$cartItems){
         $order = new Order();
        $order->user_id = Auth::id();
        $order->order_number = 'ORD-' . strtoupper(uniqid());
        $order->address_id = $address->id;
        $order->order_status = 'Processing';
        $order->total_amount = $total;
        $order->payment_method = $paymentMethod;
        $order->payment_status = 'pending';
        $order->notes = session('checkout_notes', '');
        $order->save();

        foreach ($cartItems as $item) {
            $itemTotal = $item['price'] * $item['quantity'];
            $orderItem = new OrderItem();
            $orderItem->order_id = $order->id;
            $orderItem->product_id = $item['id'];
            $orderItem->quantity = $item['quantity'];
            $orderItem->price = $item['price'];
            $orderItem->total = $itemTotal;
            $orderItem->save();
        }
        return $order;
    }
    public function paymentSuccessData($data,$order){
        $this->checkOrder($data);

         // Optional fallback: if we have session_id and no PI yet, try to resolve it now
        try {
            $sessionId = $data->get('session_id');
            if ($sessionId && empty($order->stripe_payment_intent_id)) {
                Stripe::setApiKey(config('services.stripe.secret'));
                $fullSession = \Stripe\Checkout\Session::retrieve([
                    'id' => $sessionId,
                    'expand' => ['payment_intent', 'invoice.payment_intent']
                ]);

                $paymentIntentId = null;
                if (!empty($fullSession->payment_intent)) {
                    $paymentIntentId = is_string($fullSession->payment_intent) ? $fullSession->payment_intent : ($fullSession->payment_intent->id ?? null);
                } elseif (!empty($fullSession->invoice) && isset($fullSession->invoice->payment_intent)) {
                    $paymentIntentId = is_string($fullSession->invoice->payment_intent) ? $fullSession->invoice->payment_intent : ($fullSession->invoice->payment_intent->id ?? null);
                }

                if ($paymentIntentId) {
                    $order->stripe_session_id = $sessionId;
                    $order->stripe_payment_intent_id = $paymentIntentId;
                    $order->payment_status = $order->payment_status === 'paid' ? $order->payment_status : 'paid';
                    $order->save();
                }
            }
        } catch (\Exception $e) {
            Log::warning('Checkout success page PI resolve failed: ' . $e->getMessage());
        }
    }
    public function checkOrder($data){
        $orderNumber = $data->order;
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', Auth::id())
            ->with(['items.product', 'address.district', 'address.upazila', 'address.union'])
            ->first();
             if (!$order) {
        throw new \Exception('Address not found');
    }
    return $order;
    }
    public function toPayOrder(){
        // Get all "to_pay" orders for the user
        return Order::where('user_id', Auth::id())
            ->where('order_status', 'to_pay')
            ->with(['items.product', 'address.district', 'address.upazila', 'address.union'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
    }


}
