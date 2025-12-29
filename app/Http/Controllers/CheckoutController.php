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
use App\Models\UserPaymentMethod;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
class CheckoutController extends Controller
{
    protected $siteTitle;

    function __construct()
    {
        $this->siteTitle = 'R&SMarketPlace | ';
    }

    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('home')->with('error', 'Please login to checkout');
        }

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
        $data['title'] = $this->siteTitle . 'Checkout';

        // if (count($cartItems) === 0) {
        //     return redirect()->route('cart.view')->with('error', 'Your cart is empty');
        // }

        // Get user addresses
        $addresses = DB::table('addresses')
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
            ->get();

        return view('frontend_view.pages.checkout.index', [
            'cartItems' => $cartItems,
            'addresses' => $addresses,
            'total' => $total,
            'subtotal' => $total,
            'shipping' => 0,
            'data' => $data,
        ]);
    }

    public function buyNow(Request $request)
    {

        if (!Auth::check()) {
            return redirect()->route('home')->with('error', 'Please login to proceed');
        }

        $productId = $request->input('product_id');
        $quantity = $request->input('quantity', 1);

        $product = Product::find($productId);
        if (!$product) {
            return redirect()->back()->with('error', 'Product not found');
        }

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
        $data['title'] = $this->siteTitle . 'Buy Now - Checkout';

        // Get user addresses
        $addresses = DB::table('addresses')
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
            ->get();

        return view('frontend_view.pages.checkout.index', [
            'cartItems' => $cartItems,
            'addresses' => $addresses,
            'total' => $total,
            'subtotal' => $total,
            'shipping' => 0,
            'data' => $data,
            'isBuyNow' => true,
        ]);
    }

    public function review(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('home')->with('error', 'Please login to checkout');
        }

        // Validate address selection
        $validator = Validator::make($request->all(), [
            'address_id' => 'required|exists:addresses,id',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Verify address belongs to user
        $address = DB::table('addresses')
            ->where('id', $request->address_id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$address) {
            return redirect()->back()->with('error', 'Invalid address selected');
        }

        // Store checkout data in session
        session([
            'checkout_address_id' => $request->address_id,
            'checkout_notes' => $request->notes ?? '',
            'is_buy_now' => $request->input('is_buy_now', false),
        ]);

        // Redirect to payment page
        return redirect()->route('checkout.payment');
    }

    public function payment()
    {
        if (!Auth::check()) {
            return redirect()->route('home')->with('error', 'Please login to checkout');
        }

        if (!session('checkout_address_id')) {
            return redirect()->route('checkout')->with('error', 'Please select a shipping address first');
        }

        // Check if this is a Buy Now checkout
        $isBuyNow = session('is_buy_now', false);

        if ($isBuyNow) {
            // Get items from buy now session
            $cartItems = session('buy_now_items', []);
        } else {
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
        }

        // if (count($cartItems) === 0) {
        //     return redirect()->route('cart.view')->with('error', 'Your cart is empty');
        // }

        $total = $this->calculateTotal($cartItems);
        $data['title'] = $this->siteTitle . 'Payment';

        // Get the selected address
        $address = DB::table('addresses')
            ->leftJoin('districts', 'addresses.district_id', '=', 'districts.id')
            ->leftJoin('upazilas', 'addresses.upazila_id', '=', 'upazilas.id')
            ->leftJoin('unions', 'addresses.union_id', '=', 'unions.id')
            ->where('addresses.id', session('checkout_address_id'))
            ->where('addresses.user_id', Auth::id())
            ->select(
                'addresses.*',
                'districts.name as district_name',
                'upazilas.name as upazila_name',
                'unions.name as union_name'
            )
            ->first();

        if (!$address) {
            return redirect()->route('checkout')->with('error', 'Address not found');
        }

        // Load saved payment methods for authenticated users
        $savedPaymentMethods = UserPaymentMethod::where('user_id', Auth::id())
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('frontend_view.pages.checkout.payment', [
            'total' => $total,
            'subtotal' => $total,
            'shipping' => 0,
            'address' => $address,
            'cartItems' => $cartItems,
            'savedPaymentMethods' => $savedPaymentMethods,
            'data' => $data,
        ]);
    }



    /// Helper Method
    // private function getOrCreateStripeCustomer($user){
    //     Stripe::setApiKey(config('services.stripe.secret'));
    //      /// Check if user already has a Stripe customer ID stored
    //     if($user->id){
    //         try{
    //             return Customer:retrive($user->id);
    //         }
    //     }
    // }
    public function cancel()
    {
        return redirect()->route('cart.view')->with('error', 'Your payment was cancelled.');
    }

    public function success(Request $request)
    {
        $orderNumber = $request->order;
        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', Auth::id())
            ->with(['items.product', 'address.district', 'address.upazila', 'address.union'])
            ->first();

        if (!$order) {
            return redirect()->route('home')->with('error', 'Order not found');
        }

        // Optional fallback: if we have session_id and no PI yet, try to resolve it now
        try {
            $sessionId = $request->get('session_id');
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

        return view('frontend_view.pages.checkout.success', [
            'order' => $order,
            'data' => ['title' => $this->siteTitle . 'Order Success'],
        ]);
    }

    protected function validateCheckout(array $data)
    {
        return Validator::make($data, [
            'address_id' => 'required|exists:addresses,id',
            'payment_method' => 'required|string|in:cash,bkash,card',
            'notes' => 'nullable|string',
        ]);
    }


    protected function createOrder($request, $address, $cartItems, $total, $paymentMethod)
    {
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

    protected function updateProductStock($cartItems)
    {
        foreach ($cartItems as $item) {
            $product = Product::find($item['id']);
            if ($product) {
                $product->stock -= $item['quantity'];
                $product->save();
            }
        }
    }

    public function calculateTotal($cartItems)
    {
        return array_reduce($cartItems, function($carry, $item) {
            return $carry + ($item['price'] * $item['quantity']);
        }, 0);
    }

    public function toPayOrders()
    {
        if (!Auth::check()) {
            return redirect()->route('home')->with('error', 'Please login to view your orders');
        }

        // Get all "to_pay" orders for the user
        $orders = Order::where('user_id', Auth::id())
            ->where('order_status', 'to_pay')
            ->with(['items.product', 'address.district', 'address.upazila', 'address.union'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $data['title'] = $this->siteTitle . 'Orders to Pay';

        return view('frontend_view.pages.checkout.to_pay', [
            'orders' => $orders,
            'data' => $data,
        ]);
    }

    public function completePayment(Request $request, $orderNumber)
    {
        if (!Auth::check()) {
            return redirect()->route('home')->with('error', 'Please login');
        }

        $order = Order::where('order_number', $orderNumber)
            ->where('user_id', Auth::id())
            ->where('order_status', 'to_pay')
            ->first();

        if (!$order) {
            return redirect()->back()->with('error', 'Order not found');
        }

        // Store order data in session for payment process
        session([
            'payment_order_id' => $order->id,
            'payment_order_number' => $orderNumber,
        ]);

        // Validate payment method
        $validator = Validator::make($request->all(), [
            'payment_method' => 'required|string|in:cash,bkash,card',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Update order payment method and status
            $order->payment_method = $request->payment_method;
            $order->order_status = 'confirmed';
            $order->payment_status = $request->payment_method === 'cash' ? 'pending' : 'pending';
            $order->save();

            // Clear session
            session()->forget(['payment_order_id', 'payment_order_number']);

            return redirect()->route('checkout.success', ['order' => $orderNumber])
                ->with('success', 'Order confirmed! Payment pending.');

        } catch (\Exception $e) {
            Log::error('Payment completion error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'There was an error confirming your order: ' . $e->getMessage());
        }
    }
}
