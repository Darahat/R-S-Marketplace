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
                    <span class="text-gray-500">Checkout - Step 2: Payment</span>
                </div>
            </li>
        </ol>
    </nav>

    <h1 class="text-3xl font-bold mb-6">Checkout - Step 2: Select Payment Method</h1>

    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('checkout.process') }}" method="POST" id="payment-form">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Section: Payment Methods -->
            <div class="lg:col-span-2">
                <!-- Payment Method Selection -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-semibold mb-6">Select Payment Method</h2>
                    <div class="space-y-4">
                        <!-- Cash on Delivery -->
                        <label class="flex items-center p-6 border-2 rounded-lg cursor-pointer hover:border-primary transition border-primary bg-primary/5">
                            <input type="radio" name="payment_method" value="cash"
                                   class="h-4 w-4 text-primary focus:ring-primary" checked>
                            <div class="ml-4 flex items-center flex-1">
                                <i class="fas fa-money-bill-wave text-green-600 text-2xl mr-4"></i>
                                <div>
                                    <p class="font-semibold text-gray-900">Cash on Delivery</p>
                                    <p class="text-sm text-gray-500">Pay when you receive your order</p>
                                </div>
                            </div>
                            <i class="fas fa-check-circle text-primary text-xl"></i>
                        </label>

                        <!-- bKash -->
                        <label class="flex items-center p-6 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-primary transition">
                            <input type="radio" name="payment_method" value="bkash"
                                   class="h-4 w-4 text-primary focus:ring-primary">
                            <div class="ml-4 flex items-center flex-1">
                                <i class="fas fa-mobile-alt text-pink-600 text-2xl mr-4"></i>
                                <div>
                                    <p class="font-semibold text-gray-900">bKash Payment</p>
                                    <p class="text-sm text-gray-500">Pay instantly via mobile banking</p>
                                </div>
                            </div>
                        </label>

                        <!-- Credit/Debit Card -->
                        <label class="flex items-center p-6 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-primary transition">
                            <input type="radio" name="payment_method" value="stripe"
                                   class="h-4 w-4 text-primary focus:ring-primary">
                            <div class="ml-4 flex items-center flex-1">
                                <i class="fas fa-credit-card text-blue-600 text-2xl mr-4"></i>
                                <div>
                                    <p class="font-semibold text-gray-900">Stripe</p>
                                    <p class="text-sm text-gray-500">Visa, MasterCard, American Express</p>
                                </div>
                            </div>
                        </label>
                         <input type="hidden" name="save_payment_card" value="0">

                        <label id="save_payment_card_option" class="flex items-center p-6 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-primary transition" style="display: none;">

                            <input type="checkbox" name="save_payment_card"  value="1" id="save_payment_card" class="h-4 w-4 text-primary focus:ring-primary">
                            <div class="ml-4 flex items-center flex-1">
                                 <div>
                                    <p class="font-semibold text-gray-900">Save Card</p>
                                    <p class="text-sm text-gray-500">Save Card For Future Payment</p>
                                </div>
                            </div>
                        </label>
                        <input type="hidden" name="pay_subscription" value="0">

                        <label id="subscription-option" class="flex items-center p-6 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-primary transition" style="display: none;">

                            <input type="checkbox" name="pay_subscription"  value="1" id="pay_subscription" class="h-4 w-4 text-primary focus:ring-primary">
                            <div class="ml-4 flex items-center flex-1">
                                 <div>
                                    <p class="font-semibold text-gray-900">Pay with Subscription</p>
                                    <p class="text-sm text-gray-500">Pay with Suitable installment</p>
                                </div>
                            </div>
                        </label>

                    </div>
                </div>

                <!-- Back Button -->
                <div class="mt-6 flex gap-3">
                    <a href="{{ route('checkout') }}" class="flex-1 text-center px-6 py-3 border-2 border-primary text-primary font-bold rounded-lg hover:bg-primary/5 transition">
                        <i class="fas fa-arrow-left mr-2"></i>Back
                    </a>
                    <button type="submit" class="flex-1 bg-primary hover:bg-secondary text-white font-bold py-3 px-6 rounded-lg transition transform hover:scale-[1.02] flex items-center justify-center">
                        <i class="fas fa-lock mr-2"></i>Complete Payment
                    </button>
                </div>
            </div>

            <!-- Right Section: Order Total -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-lg shadow-md p-6 sticky top-4">
                    <h2 class="text-xl font-semibold mb-6">Order Total</h2>

                    <!-- Total Amount Display -->
                    <div class="bg-gray-50 rounded-lg p-6 text-center border-2 border-gray-200">
                        <p class="text-gray-600 text-sm mb-2">Total Amount to Pay</p>
                        <p class="text-4xl font-bold text-primary">৳{{ number_format($total, 2) }}</p>
                    </div>

                    <!-- Payment Details -->
                    <div class="mt-6 space-y-4 pb-6 border-b">
                        <div class="flex justify-between text-gray-600 text-sm">
                            <span>Subtotal:</span>
                            <span>৳{{ number_format($subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-gray-600 text-sm">
                            <span>Shipping:</span>
                            <span>{{ $shipping > 0 ? '৳' . number_format($shipping, 2) : 'Free' }}</span>
                        </div>
                    </div>

                    <!-- Shipping Address Summary -->
                    <div class="mt-6">
                        <h3 class="font-semibold text-gray-900 mb-3">Shipping To</h3>
                        <div class="bg-gray-50 rounded-lg p-4 text-sm">
                            <p class="font-medium text-gray-900">{{ $address->full_name }}</p>
                            <p class="text-gray-600">{{ $address->street_address }}</p>
                            <p class="text-gray-600">{{ $address->union_name }}, {{ $address->upazila_name }}</p>
                            <p class="text-gray-600">{{ $address->district_name }} - {{ $address->postal_code }}</p>
                            <p class="text-gray-600 mt-2">{{ $address->phone }}</p>
                        </div>
                    </div>

                    <!-- Security Badge -->
                    <div class="mt-6 p-4 bg-green-50 rounded-lg border border-green-200 text-center">
                        <p class="text-xs text-green-800">
                            <i class="fas fa-lock mr-2"></i>
                            100% Secure Payment
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
    // Handle payment method selection
    $('input[name="payment_method"]').on('change', function() {
        // Reset all labels
        $('label').removeClass('border-primary bg-primary/5');

        // Remove all check circles
        $('label i.fa-check-circle').remove();

        // Add styles and check to selected
        const selectedLabel = $(this).closest('label');
        selectedLabel.addClass('border-primary bg-primary/5');
        selectedLabel.append('<i class="fas fa-check-circle text-primary text-xl"></i>');

        // Show/hide subscription option based on Stripe selection
        if ($(this).val() === 'stripe') {
            $('#subscription-option').slideDown(300);
            $('#save_payment_card_option').slideDown(300);

        } else {
            $('#subscription-option').slideUp(300);
            $('#pay_subscription').prop('checked', false);
            $('#save_payment_card_option').slideUp(300);
            $('#save_payment_card').prop('checked', false);


        }
    });

    // Initialize first radio button as selected
    $('input[name="payment_method"][value="cash"]').prop('checked', true).trigger('change');

    $('#payment-form').on('submit', function(e) {
        const paymentMethod = $('input[name="payment_method"]:checked').val();
        if (!paymentMethod) {
            e.preventDefault();
            alert('Please select a payment method');
            return false;
        }
    });
});
</script>
@endpush
@endsection
