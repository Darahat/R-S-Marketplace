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
                    <span class="text-gray-500">Orders to Pay</span>
                </div>
            </li>
        </ol>
    </nav>

    <h1 class="text-3xl font-bold mb-6">Orders to Pay</h1>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($orders->count() > 0)
        <div class="space-y-6">
            @foreach ($orders as $order)
            <div class="bg-white rounded-lg shadow-md p-6">
                <!-- Order Header -->
                <div class="flex items-center justify-between mb-4 pb-4 border-b">
                    <div>
                        <h3 class="text-xl font-semibold text-gray-900">{{ $order->order_number }}</h3>
                        <p class="text-sm text-gray-500">{{ $order->created_at->format('M d, Y H:i') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-600 mb-1">Amount Due</p>
                        <p class="text-2xl font-bold text-primary">৳{{ number_format($order->total_amount, 2) }}</p>
                    </div>
                </div>

                <!-- Order Items -->
                <div class="mb-4">
                    <h4 class="font-semibold text-gray-900 mb-3">Items</h4>
                    <div class="space-y-2">
                        @foreach ($order->items as $item)
                        <div class="flex items-center justify-between text-sm">
                            <div class="flex items-center flex-1">
                                <img src="{{ asset($item->product->image) }}" alt="{{ $item->product->name }}"
                                     class="w-10 h-10 object-cover rounded mr-3">
                                <div>
                                    <p class="font-medium text-gray-900">{{ $item->product->name }}</p>
                                    <p class="text-gray-500">Qty: {{ $item->quantity }}</p>
                                </div>
                            </div>
                            <p class="font-semibold">৳{{ number_format($item->price * $item->quantity, 2) }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- Shipping Address -->
                <div class="mb-4 pb-4 border-b">
                    <h4 class="font-semibold text-gray-900 mb-2">Shipping To</h4>
                    <p class="text-sm text-gray-600">
                        {{ $order->address->full_name }}<br>
                        {{ $order->address->street_address }}<br>
                        {{ $order->address->union_name }}, {{ $order->address->upazila_name }}<br>
                        {{ $order->address->district_name }} - {{ $order->address->postal_code }}<br>
                        {{ $order->address->phone }}
                    </p>
                </div>

                <!-- Payment Method -->
                <div class="mb-4">
                    <h4 class="font-semibold text-gray-900 mb-3">Select Payment Method</h4>
                    <form action="{{ route('checkout.complete_payment', $order->order_number) }}" method="POST" class="space-y-2">
                        @csrf

                        <label class="flex items-center p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-primary transition">
                            <input type="radio" name="payment_method" value="cash" class="h-4 w-4 text-primary" checked>
                            <div class="ml-3 flex items-center">
                                <i class="fas fa-money-bill-wave text-green-600 text-lg mr-2"></i>
                                <div>
                                    <p class="font-medium text-gray-900">Cash on Delivery</p>
                                    <p class="text-xs text-gray-500">Pay when you receive</p>
                                </div>
                            </div>
                        </label>

                        <label class="flex items-center p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-primary transition">
                            <input type="radio" name="payment_method" value="bkash" class="h-4 w-4 text-primary">
                            <div class="ml-3 flex items-center">
                                <i class="fas fa-mobile-alt text-pink-600 text-lg mr-2"></i>
                                <div>
                                    <p class="font-medium text-gray-900">bKash</p>
                                    <p class="text-xs text-gray-500">Mobile payment</p>
                                </div>
                            </div>
                        </label>

                        <label class="flex items-center p-3 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-primary transition">
                            <input type="radio" name="payment_method" value="stripe" class="h-4 w-4 text-primary">
                            <div class="ml-3 flex items-center">
                                <i class="fas fa-credit-card text-blue-600 text-lg mr-2"></i>
                                <div>
                                    <p class="font-medium text-gray-900">Stripe</p>
                                    <p class="text-xs text-gray-500">Visa, MasterCard</p>
                                </div>
                            </div>
                        </label>

                        <div class="flex gap-3 pt-3">
                            <button type="submit" class="flex-1 bg-primary hover:bg-secondary text-white font-bold py-2 px-4 rounded-lg transition">
                                <i class="fas fa-check mr-2"></i>Complete Payment
                            </button>
                            <a href="{{ route('customer.orders') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                                <i class="fas fa-arrow-left mr-2"></i>Back
                            </a>
                        </div>
                    </form>
                </div>

                @if($order->notes)
                <div class="text-sm">
                    <p class="font-semibold text-gray-900 mb-1">Notes</p>
                    <p class="text-gray-600">{{ $order->notes }}</p>
                </div>
                @endif
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $orders->links() }}
        </div>
    @else
        <div class="bg-white rounded-lg shadow-md p-12 text-center">
            <i class="fas fa-inbox text-gray-300 text-5xl mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No Pending Payments</h3>
            <p class="text-gray-600 mb-6">You don't have any orders waiting for payment</p>
            <a href="{{ route('home') }}" class="inline-block px-6 py-3 bg-primary hover:bg-secondary text-white font-bold rounded-lg">
                <i class="fas fa-shopping-bag mr-2"></i>Continue Shopping
            </a>
        </div>
    @endif
</div>

@push('scripts')
<script>
$(document).ready(function() {
    // Update payment method selection UI
    $('input[name="payment_method"]').on('change', function() {
        $(this).closest('form').find('label').removeClass('border-primary bg-primary/5');
        $(this).closest('label').addClass('border-primary bg-primary/5');
    });
});
</script>
@endpush
@endsection
