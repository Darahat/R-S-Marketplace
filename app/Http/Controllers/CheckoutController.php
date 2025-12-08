<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    protected $siteTitle;
    
    function __construct()
    {
        $this->siteTitle = 'MarketGhor | ';
    }

    public function index()
    {
        $cartItems = session('cart', []);
        $total = $this->calculateTotal($cartItems);
        $data['title'] = $this->siteTitle . 'Checkout';
        
        if (count($cartItems) === 0) {
            return redirect()->route('cart.view')->with('error', 'Your cart is empty');
        }
        // // Check product availability
        // foreach ($cartItems as $item) {
        //     $product = Product::find($item['id']);
        //     if (!$product || $product->stock < $item['quantity']) {
        //         return redirect()->route('cart.view')->with('error', "{$item['name']} is out of stock or quantity not available");
        //     }
        // }
        return view('frontend_view.pages.checkout.index', [
            'cartItems' => $cartItems,
            'total' => $total,
            'data' => $data,
        ]);
    }
    
    public function process(Request $request)
    {
        $cartItems = session('cart', []);
        
        if (count($cartItems) === 0) {
            return redirect()->route('cart.view')->with('error', 'Your cart is empty');
        }
        
        // Validate checkout data
        $validator = $this->validateCheckout($request->all());
        
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Verify product availability again
        foreach ($cartItems as $item) {
            $product = Product::find($item['id']);
            if (!$product || $product->stock < $item['quantity']) {
                return redirect()->route('cart.view')->with('error', "{$item['name']} is no longer available in the requested quantity");
            }
        }
        
        try {
            // Process payment
            $paymentMethod = $request->payment_method;
            $total = $this->calculateTotal($cartItems);
            
            if ($paymentMethod === 'credit_card') {
                $this->processStripePayment($request, $total);
            } elseif ($paymentMethod === 'paypal') {
                // PayPal integration would go here
                // For now, we'll just mark as pending
            }
            
            // Create the order
            $order = $this->createOrder($request, $cartItems, $total, $paymentMethod);
            
            // Update product stock
            $this->updateProductStock($cartItems);
            
            // Clear the cart
            session()->forget('cart');
            
            // Send order confirmation email
            // $this->sendOrderConfirmation($order);
            
            return redirect()->route('checkout.success', ['order' => $order->order_number])
                ->with('success', 'Order placed successfully!');
                
        } catch (\Exception $e) {
            Log::error('Checkout error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'There was an error processing your payment: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    protected function validateCheckout(array $data)
    {
        return Validator::make($data, [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'country' => 'required|string|max:255',
            'payment_method' => 'required|string|in:credit_card,paypal',
            'stripeToken' => 'required_if:payment_method,credit_card',
            'notes' => 'nullable|string',
        ], [
            'stripeToken.required_if' => 'The Stripe token is required for credit card payments',
        ]);
    }
    
    protected function processStripePayment(Request $request, $amount)
    {
        try {
            $charge = Charge::create([
                'amount' => $amount * 100, // Stripe uses cents
                'currency' => 'usd',
                'source' => $request->stripeToken,
                'description' => 'Order payment',
            ]);
            
            if (!$charge->paid) {
                throw new \Exception('Payment was not successful');
            }
            
            return $charge;
        } catch (\Exception $e) {
            throw new \Exception('Stripe payment error: ' . $e->getMessage());
        }
    }
    
    protected function createOrder(Request $request, $cartItems, $total, $paymentMethod)
    {
        $order = new Order();
        $order->user_id = Auth::id();
        $order->order_number = 'ORD-' . strtoupper(uniqid());
        $order->status = 'processing';
        $order->grand_total = $total;
        $order->item_count = count($cartItems);
        $order->payment_method = $paymentMethod;
        $order->payment_status = $paymentMethod === 'credit_card' ? 'paid' : 'pending';
        $order->first_name = $request->first_name;
        $order->last_name = $request->last_name;
        $order->email = $request->email;
        $order->phone = $request->phone;
        $order->address = $request->address;
        $order->city = $request->city;
        $order->country = $request->country;
        $order->notes = $request->notes;
        $order->save();
        
        foreach ($cartItems as $item) {
            $orderItem = new OrderItem();
            $orderItem->order_id = $order->id;
            $orderItem->product_id = $item['id'];
            $orderItem->quantity = $item['quantity'];
            $orderItem->price = $item['price'];
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
    
    private function calculateTotal($cartItems)
    {
        return array_reduce($cartItems, function($carry, $item) {
            return $carry + ($item['price'] * $item['quantity']);
        }, 0);
    }
}

