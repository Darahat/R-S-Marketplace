<?php

namespace App\Http\Controllers;

use App\Http\Requests\BuyNowRequest;
use App\Http\Requests\CheckoutRequest;
use App\Http\Requests\OrderSuccessRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Cart;

use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\UserPaymentMethod;
use App\Services\CheckoutService;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
class CheckoutController extends Controller
{
    protected $siteTitle;

    function __construct(protected CheckoutService $checkout_service)
    {
        $this->siteTitle = 'R&SMarketPlace | ';
    }

    public function index()
    {
        if (!Auth::check()) {
            return redirect()->route('home')->with('error', 'Please login to checkout');
        }

    $checkoutindex = $this->checkout_service->index();
        if (($checkoutindex['isEmptyCart'] ?? false) === true) {
            return redirect()->route('cart.view')->with('error', 'Your cart is empty');
        }

    $data['title'] = $this->siteTitle . 'Checkout';
        return view('frontend_view.pages.checkout.index', [
            'cartItems' => $checkoutindex['cartItems'],
            'addresses' => $checkoutindex['addresses'],
            'defaultAddressId' => $checkoutindex['defaultAddressId'] ?? null,
            'total' => $checkoutindex['total'],
            'subtotal' => $checkoutindex['total'],
            'shipping' => 0,
            'data' => $data,
        ]);
    }

    public function buyNow(BuyNowRequest $request)
    {
        if (!Auth::check()) {
            return redirect()->route('home')->with('error', 'Please login to proceed');
        }

        $product = Product::find($request->validated('product_id'));
        $quantity = $request->validated('quantity');


        $data['title'] = $this->siteTitle . 'Buy Now - Checkout';
        $response = $this->checkout_service->buyNow($product,$quantity);

        if (!$response['hasAddresses']) {
            return redirect()->route('customer.addresses.index')
                ->with('error', 'Please add a shipping address before using Buy Now.');
        }

        return view('frontend_view.pages.checkout.index', [
            'cartItems' => $response['cartItems'],
            'addresses' => $response['addresses'],
            'defaultAddressId' => $response['defaultAddressId'] ?? null,
            'total' => $response['total'],
            'subtotal' => $response['total'],
            'shipping' => 0,
            'data' => $data,
            'isBuyNow' => true,
        ]);
    }

    public function review(CheckoutRequest $request)
    {
        if (!Auth::check()) {
            return redirect()->route('home')->with('error', 'Please login to checkout');
        }


        $address = $this->checkout_service->review($request->validated());


        if (!$address) {
            return redirect()->back()->with('error', 'Invalid address selected');
        }

        // Store checkout data in session


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
        $data['title'] = $this->siteTitle . 'Payment';

        $checkoutData = $this->checkout_service->getPaymentPageData();
        if (($checkoutData['isEmptyCart'] ?? false) === true) {
            return redirect()->route('cart.view')->with('error', 'Your cart is empty');
        }

        return view('frontend_view.pages.checkout.payment', [
            'total' => $checkoutData['total'],
            'subtotal' => $checkoutData['total'],
            'shipping' => 0,
            'address' => $checkoutData['address'],
            'cartItems' => $checkoutData['cartItems'],
            'savedPaymentMethods' => $checkoutData['savedPaymentMethods'],
            'data' => $data,
        ]);
    }

    public function cancel()
    {
        return redirect()->route('cart.view')->with('error', 'Your payment was cancelled.');
    }

    public function success(OrderSuccessRequest $request)
    {
        // dd($request->all());
        $data = $request->validated();
        $order = $this->checkout_service->paymentSuccessData($data);
        //dd($order->address->union);
        return view('frontend_view.pages.checkout.success', [
            'order' => $order,
            'data' => ['title' => $this->siteTitle . 'Order Success'],
        ]);
    }
    protected function createOrder( $address, $cartItems, $total, $paymentMethod)
    {

        $order = $this->checkout_service->createOrderData($address,$total,$paymentMethod,$cartItems);
        return $order;
    }



    public function toPayOrders()
    {
        if (!Auth::check()) {
            return redirect()->route('home')->with('error', 'Please login to view your orders');
        }

        $orders = $this->checkout_service->toPayOrder();

        $data['title'] = $this->siteTitle . 'Orders to Pay';

        return view('frontend_view.pages.checkout.to_pay', [
            'orders' => $orders,
            'data' => $data,
        ]);
    }



}
