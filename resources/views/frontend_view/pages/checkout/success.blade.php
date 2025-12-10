@extends('frontend_view.layouts.home')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <nav class="flex mb-6" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('home') }}" class="text-gray-700 hover:text-primary">
                    <i class="fas fa-home mr-2"></i>Home
                </a>
            </li>
            <li>
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                    <span class="text-gray-500">Order Successful</span>
                </div>
            </li>
        </ol>
    </nav>

    <!-- Success Message -->
    <div class="max-w-2xl mx-auto">
        <!-- Success Icon -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-24 h-24 bg-green-100 rounded-full mb-4">
                <i class="fas fa-check text-green-600 text-5xl"></i>
            </div>
            <h1 class="text-4xl font-bold text-gray-900 mb-2">Order Successful!</h1>
            <p class="text-gray-600 text-lg">Thank you for your purchase</p>
        </div>

        <!-- Order Details Card -->
        <div class="bg-white rounded-lg shadow-lg p-8 mb-6">
            <h2 class="text-2xl font-semibold mb-6">Order Details</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6 pb-6 border-b">
                <div>
                    <p class="text-gray-600 text-sm mb-1">Order Number</p>
                    <p class="text-xl font-bold text-gray-900">{{ $order->order_number }}</p>
                </div>
                <div>
                    <p class="text-gray-600 text-sm mb-1">Order Date</p>
                    <p class="text-xl font-bold text-gray-900">{{ $order->created_at->format('M d, Y') }}</p>
                </div>
                <div>
                    <p class="text-gray-600 text-sm mb-1">Order Status</p>
                    <div class="inline-flex items-center">
                        <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-semibold capitalize">
                            {{ str_replace('_', ' ', $order->order_status) }}
                        </span>
                    </div>
                </div>
                <div>
                    <p class="text-gray-600 text-sm mb-1">Payment Method</p>
                    <p class="text-lg font-semibold text-gray-900 capitalize">{{ str_replace('_', ' ', $order->payment_method) }}</p>
                </div>
            </div>

            <!-- Order Items -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold mb-4">Items Ordered</h3>
                <div class="space-y-4">
                    @foreach($order->items as $item)
                    <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center flex-1">
                            <img src="{{ asset($item->product->image) }}" alt="{{ $item->product->name }}"
                                 class="w-16 h-16 object-cover rounded mr-4">
                            <div>
                                <h4 class="font-semibold text-gray-900">{{ $item->product->name }}</h4>
                                <p class="text-gray-600 text-sm">Quantity: {{ $item->quantity }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-600">৳{{ number_format($item->price, 2) }} each</p>
                            <p class="text-lg font-semibold text-gray-900">৳{{ number_format($item->price * $item->quantity, 2) }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <!-- Price Summary -->
            <div class="bg-gray-50 rounded-lg p-4 mb-6">
                <div class="flex justify-between mb-2">
                    <span class="text-gray-600">Subtotal</span>
                    <span class="font-semibold text-gray-900">৳{{ number_format($order->total_amount, 2) }}</span>
                </div>
                <div class="flex justify-between pt-2 border-t">
                    <span class="text-lg font-semibold text-gray-900">Total Amount</span>
                    <span class="text-2xl font-bold text-primary">৳{{ number_format($order->total_amount, 2) }}</span>
                </div>
            </div>

            <!-- Shipping Address -->
            <div class="mb-6 pb-6 border-b">
                <h3 class="font-semibold text-gray-900 mb-3">Shipping Address</h3>
                <div class="bg-gray-50 rounded-lg p-4 text-sm">
                    <p class="font-medium text-gray-900">{{ $order->address->full_name }}</p>
                    <p class="text-gray-600">{{ $order->address->street_address }}</p>
                    <p class="text-gray-600">{{ $order->address->union->name }}, {{ $order->address->upazila->name }}</p>
                    <p class="text-gray-600">{{ $order->address->district->name }} - {{ $order->address->postal_code }}</p>
                    <p class="text-gray-600">{{ $order->address->phone }}</p>
                </div>
            </div>

            <!-- Notes -->
            @if($order->notes)
            <div class="mb-6">
                <h3 class="font-semibold text-gray-900 mb-2">Order Notes</h3>
                <p class="text-gray-600 bg-gray-50 p-4 rounded-lg">{{ $order->notes }}</p>
            </div>
            @endif
        </div>

        <!-- Action Buttons -->
        <div class="flex gap-4 justify-center">
            <a href="{{ route('checkout.to_pay') }}" class="px-8 py-3 bg-primary hover:bg-secondary text-white font-bold rounded-lg transition flex items-center">
                <i class="fas fa-credit-card mr-2"></i>Complete Payment
            </a>
            <a href="{{ route('home') }}" class="px-8 py-3 border-2 border-primary text-primary hover:bg-primary/5 font-bold rounded-lg transition flex items-center">
                <i class="fas fa-shopping-bag mr-2"></i>Continue Shopping
            </a>
        </div>

        <!-- Info Message -->
        <div class="mt-8 p-6 bg-amber-50 rounded-lg border border-amber-200">
            <h4 class="font-semibold text-amber-900 mb-2">
                <i class="fas fa-hourglass-half mr-2"></i>Order Created - Awaiting Payment
            </h4>
            <ul class="text-amber-800 text-sm space-y-1">
                <li><i class="fas fa-check mr-2"></i>Your order has been successfully created</li>
                <li><i class="fas fa-check mr-2"></i>Now you need to complete the payment</li>
                <li><i class="fas fa-check mr-2"></i>Click "Complete Payment" to select your payment method</li>
                <li><i class="fas fa-check mr-2"></i>For Cash on Delivery, the payment will be collected upon delivery</li>
                <li><i class="fas fa-check mr-2"></i>You can manage all pending payments in your "Orders to Pay" section</li>
            </ul>
        </div>
    </div>
</div>

@endsection
