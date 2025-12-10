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
                    <span class="text-gray-500">Checkout - Step 1: Shipping</span>
                </div>
            </li>
        </ol>
    </nav>

    <h1 class="text-3xl font-bold mb-6">Checkout - Step 1: Shipping Details</h1>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('checkout.review') }}" method="POST" id="checkout-form">
        @csrf
        <input type="hidden" name="is_buy_now" value="{{ $isBuyNow ?? false }}">

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Section: Shipping & Notes -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Shipping Address -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-semibold">Shipping Address</h2>
                        <a href="{{ route('customer.address') }}" class="text-primary hover:text-secondary text-sm font-medium">
                            <i class="fas fa-plus mr-1"></i>Add New Address
                        </a>
                    </div>

                    @if($addresses->count() > 0)
                        <div class="space-y-3">
                            @foreach($addresses as $address)
                            <label class="flex items-start p-4 border-2 rounded-lg cursor-pointer hover:border-primary transition {{ $address->is_default ? 'border-primary bg-primary/5' : 'border-gray-200' }}">
                                <input type="radio" name="address_id" value="{{ $address->id }}"
                                       class="mt-1 h-4 w-4 text-primary focus:ring-primary"
                                       {{ $address->is_default ? 'checked' : '' }} required>
                                <div class="ml-3 flex-1">
                                    <div class="flex items-center justify-between">
                                        <h3 class="font-medium text-gray-900">{{ $address->full_name }}</h3>
                                        @if($address->is_default)
                                        <span class="text-xs bg-primary text-white px-2 py-1 rounded">Default</span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">{{ $address->phone }}</p>
                                    <p class="text-sm text-gray-600">{{ $address->street_address }}</p>
                                    <p class="text-sm text-gray-600">
                                        {{ $address->union_name }}, {{ $address->upazila_name }},
                                        {{ $address->district_name }} - {{ $address->postal_code }}
                                    </p>
                                    <p class="text-sm text-gray-600">{{ $address->country }}</p>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 border-2 border-dashed rounded-lg">
                            <i class="fas fa-map-marker-alt text-4xl text-gray-300 mb-3"></i>
                            <p class="text-gray-500 mb-3">No shipping address found</p>
                            <a href="{{ route('customer.address') }}" class="inline-flex items-center px-4 py-2 bg-primary hover:bg-secondary text-white font-medium rounded-lg transition">
                                <i class="fas fa-plus mr-2"></i>Add Shipping Address
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Order Notes -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold mb-4">Order Notes (Optional)</h2>
                    <textarea name="notes" rows="4"
                              class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                              placeholder="Special instructions for your order...">{{ old('notes') }}</textarea>
                </div>

                <!-- Continue Button -->
                <button type="submit"
                        class="w-full bg-primary hover:bg-secondary text-white font-bold py-3 px-4 rounded-lg transition transform hover:scale-[1.02] flex items-center justify-center"
                        {{ $addresses->count() == 0 ? 'disabled' : '' }}>
                    <i class="fas fa-arrow-right mr-2"></i>
                    Continue to Payment
                </button>
            </div>

            <!-- Right Section: Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-4">
                    <h2 class="text-xl font-semibold mb-4">Order Summary</h2>

                    <!-- Product List -->
                    <div class="space-y-4 mb-4 max-h-96 overflow-y-auto">
                        @foreach($cartItems as $item)
                        <div class="flex items-center space-x-3 pb-3 border-b">
                            <img src="{{ asset($item['image']) }}" alt="{{ $item['name'] }}"
                                 class="w-16 h-16 object-cover rounded">
                            <div class="flex-1 min-w-0">
                                <h3 class="text-sm font-medium text-gray-900 truncate">{{ $item['name'] }}</h3>
                                <p class="text-sm text-gray-500">Qty: {{ $item['quantity'] }}</p>
                            </div>
                            <p class="text-sm font-semibold text-gray-900">
                                ৳{{ number_format($item['price'] * $item['quantity'], 2) }}
                            </p>
                        </div>
                        @endforeach
                    </div>

                    <!-- Price Summary -->
                    <div class="space-y-3 pt-4 border-t">
                        <div class="flex justify-between text-gray-600">
                            <span>Subtotal</span>
                            <span>৳{{ number_format($subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-gray-600">
                            <span>Shipping</span>
                            <span>{{ $shipping > 0 ? '৳' . number_format($shipping, 2) : 'Free' }}</span>
                        </div>
                        <div class="flex justify-between text-lg font-bold text-gray-900 pt-3 border-t">
                            <span>Total</span>
                            <span class="text-primary">৳{{ number_format($total + $shipping, 2) }}</span>
                        </div>
                    </div>

                    <!-- Info -->
                    <div class="mt-6 p-4 bg-blue-50 rounded-lg border border-blue-200">
                        <p class="text-xs text-blue-800">
                            <i class="fas fa-info-circle mr-2"></i>
                            You will select your payment method on the next step
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    $('#checkout-form').on('submit', function(e) {
        const addressSelected = $('input[name="address_id"]:checked').length > 0;
        if (!addressSelected) {
            e.preventDefault();
            alert('Please select a shipping address');
            return false;
        }
    });
});
</script>
@endpush
@endsection
