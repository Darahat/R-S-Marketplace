<?php

namespace App\Http\Controllers;

use App\Http\Requests\BuyNowRequest;
use App\Http\Requests\CheckoutRequest;
use App\Http\Requests\OrderSuccessRequest;
use Illuminate\Support\Facades\Auth;

use App\Models\Product;
use App\Services\CheckoutService;
use App\Services\OrderService;
class CheckoutController extends Controller
{
    protected $siteTitle;

    function __construct(
        protected CheckoutService $checkout_service,
        protected OrderService $order_service,
    )
    {
        $this->siteTitle = 'R&SMarketPlace | ';
    }

    public function index()
    {
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
        $data = $request->validated();
        $order = $this->checkout_service->paymentSuccessData($data);
        return view('frontend_view.pages.checkout.success', [
            'order' => $order,
            'data' => ['title' => $this->siteTitle . 'Order Success'],
        ]);
    }

    public function toPayOrders()
    {
        $orders = $this->order_service->getToPayOrders((int) Auth::id(), 10);

        $data['title'] = $this->siteTitle . 'Orders to Pay';

        return view('frontend_view.pages.checkout.to_pay', [
            'orders' => $orders,
            'data' => $data,
        ]);
    }



}
