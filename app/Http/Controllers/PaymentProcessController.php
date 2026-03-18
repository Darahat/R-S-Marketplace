<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\PaymentProcessRequest;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Stripe\Stripe;
 use Stripe\Customer;
 use App\Services\CheckoutService;
use App\Services\PaymentProcessService;
use App\Http\Requests\CompletePaymentRequest;
use Stripe\Checkout\Session as StripeSession;

class PaymentProcessController extends Controller
{
      function __construct(protected PaymentProcessRequest $request,protected CheckoutService $checkout_service,protected PaymentProcessService $payment_process_service)
    {

    }
public function process(PaymentProcessRequest $request)
    {
       if (!Auth::check()) {
           return redirect()->route('home')->with('error', 'Please login to checkout');
       }

       if (!session('checkout_address_id')) {
           return redirect()->route('checkout')->with('error', 'Please select a shipping address first');
       }
        $reqData = $request->validated();
        try {
        $payment_method = $reqData['payment_method'];
        $data = $this->payment_process_service->process($reqData);
  Log::info($data);

        if ($payment_method === 'stripe') {
                return $this->processStripePayment($reqData, $data['address'], $data['cartItems'], $data['total'], $data['is_pay_subscription']);
            } else {
                return $this->processNonStripePayment($reqData, $data['address'], $data['cartItems'],  $data['total'], $payment_method, $data['isBuyNow']);
            }
        } catch (\Exception $e) {
            Log::error('Checkout errorrr: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'There was an error processing your order: ' . $e->getMessage())
                ->withInput();
        }
    }



    private function processStripePayment(array $data, $address, $cartItems, $total, $is_pay_subscription)
    {
        $session = $this->payment_process_service->stripePaymentProcess($data, $address, $cartItems, $total, $is_pay_subscription);
        return redirect($session->url);
    }

    private function processNonStripePayment($request, $address, $cartItems, $total, $payment_method, $isBuyNow)
    {
        $order = $this->checkout_service->createOrderData($address, $total, $payment_method, $cartItems);
        $this->checkout_service->updateProductStock($cartItems);

        // Create payment record for cash/bkash
        $this->payment_process_service->paymentCreate($order->id,$payment_method,$total);

        $this->payment_process_service->clearCartAndSession($isBuyNow);

        return redirect()->route('checkout.success', ['order' => $order->order_number])
            ->with('success', 'Order placed successfully!');
    }



    public function completePayment(CompletePaymentRequest $request, $orderNumber)
    {

        $order = $this->checkout_service->toCheckSingleOrder($orderNumber);
        // Store order data in session for payment process
        $validData= $request->validated();

        try {
            $this->payment_process_service->completePayment($order,$validData);

            return redirect()->route('checkout.success', ['order' => $orderNumber])
                ->with('success', 'Order confirmed! Payment pending.');

        } catch (\Exception $e) {
            Log::error('Payment completion error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'There was an error confirming your order: ' . $e->getMessage());
        }
    }
}
