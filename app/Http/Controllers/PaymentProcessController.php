<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\PaymentProcessRequest;
use App\Services\OrderService;
use App\Services\StockManagementService;
use App\Services\PaymentProcessService;
use App\Http\Requests\CompletePaymentRequest;

class PaymentProcessController extends Controller
{
    function __construct(
        protected OrderService $order_service,
        protected StockManagementService $stock_service,
        protected PaymentProcessService $payment_process_service
    )
    {

    }
public function process(PaymentProcessRequest $request)
    {
       if (!session('checkout_address_id')) {
           return redirect()->route('checkout')->with('error', 'Please select a shipping address first');
       }
        $reqData = $request->validated();
        try {
        $payment_method = $reqData['payment_method'];
        $data = $this->payment_process_service->process($reqData);

        if ($payment_method === 'stripe') {
                return $this->processStripePayment($reqData, $data['address'], $data['cartItems'], $data['total'], $data['is_pay_subscription']);
            } else {
                return $this->processNonStripePayment($data['address'], $data['cartItems'],  $data['total'], $payment_method, $data['isBuyNow']);
            }
        } catch (\Exception $e) {
            Log::error('Checkout error: ' . $e->getMessage());
            return redirect()->back()
    ->with('error', 'There was an error processing your order. Please try again.')
    ->withInput();
        }
    }



    private function processStripePayment(array $data, $address, $cartItems, $total, $is_pay_subscription)
    {
        $session = $this->payment_process_service->stripePaymentProcess($data, $address, $cartItems, $total, $is_pay_subscription);
        return redirect($session->url);
    }

    private function processNonStripePayment($address, $cartItems, $total, $payment_method, $isBuyNow)
    {
        $orderStatus = 'Processing';
        $order = $this->order_service->createOrder(
            Auth::id(),
            [
                'address_id' => $address->id,
                'order_status' => $orderStatus,
                'total_amount' => $total,
                'payment_method' => $payment_method,
                'payment_status' => 'pending',
                'notes' => session('checkout_notes', ''),
            ],
            $cartItems
        );

        $this->stock_service->decrementStock($cartItems);

        // Create payment record for cash/bkash
        $this->payment_process_service->paymentCreate($order->id,$payment_method,$total);

        $this->payment_process_service->clearCartAndSession($isBuyNow);

        return redirect()->route('checkout.success', ['order' => $order->order_number])
            ->with('success', 'Order placed successfully!');
    }



    public function completePayment(CompletePaymentRequest $request, $orderNumber)
    {
        // Find order for payment
        $order = $this->order_service->findOrderForPayment((string) $orderNumber, (int) Auth::id());

        if (!$order) {
            return redirect()->route('checkout.to_pay')
                ->with('error', 'Order not found or is not eligible for payment.');
        }

        // Store order data in session for payment process
        session([
            'payment_order_id' => $order->id,
            'payment_order_number' => $orderNumber,
        ]);

        $validData = $request->validated();

        try {
            $this->payment_process_service->completePayment($order, $validData);

            return redirect()->route('checkout.success', ['order' => $orderNumber])
                ->with('success', 'Order confirmed! Payment pending.');

        } catch (\Exception $e) {
            Log::error('Payment completion error: ' . $e->getMessage());
            return redirect()->back()
    ->with('error', 'There was an error confirming your order. Please contact support.');
        }
    }
}
