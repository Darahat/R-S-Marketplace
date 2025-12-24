@extends('frontend_view.layout.master')

@section('page_title', 'Saved Payment Methods')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-4xl">
    <h1 class="text-3xl font-bold mb-6">Saved Payment Methods</h1>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            {{ session('error') }}
        </div>
    @endif

    @if($paymentMethods->isEmpty())
        <div class="bg-white rounded-lg shadow-md p-8 text-center">
            <i class="fas fa-credit-card text-gray-400 text-6xl mb-4"></i>
            <h2 class="text-xl font-semibold text-gray-700 mb-2">No Saved Payment Methods</h2>
            <p class="text-gray-600 mb-6">Save a payment method during checkout for faster payments in the future.</p>
            <a href="{{ route('home') }}" class="inline-block bg-primary text-white px-6 py-3 rounded-lg hover:bg-primary/90 transition">
                Continue Shopping
            </a>
        </div>
    @else
        <div class="space-y-4">
            @foreach($paymentMethods as $method)
                <div class="bg-white rounded-lg shadow-md p-6 flex items-center justify-between {{ $method->isExpired() ? 'border-2 border-red-300' : '' }}">
                    <div class="flex items-center space-x-4">
                        <!-- Card Icon -->
                        <div class="text-4xl">
                            @if($method->card_brand == 'visa')
                                <i class="fab fa-cc-visa text-blue-600"></i>
                            @elseif($method->card_brand == 'mastercard')
                                <i class="fab fa-cc-mastercard text-red-600"></i>
                            @elseif($method->card_brand == 'amex')
                                <i class="fab fa-cc-amex text-blue-500"></i>
                            @elseif($method->card_brand == 'discover')
                                <i class="fab fa-cc-discover text-orange-600"></i>
                            @else
                                <i class="fas fa-credit-card text-gray-600"></i>
                            @endif
                        </div>

                        <!-- Card Details -->
                        <div>
                            <div class="flex items-center space-x-2">
                                <h3 class="text-lg font-semibold">{{ $method->card_display }}</h3>
                                @if($method->is_default)
                                    <span class="bg-green-100 text-green-800 text-xs font-semibold px-2 py-1 rounded">Default</span>
                                @endif
                                @if($method->isExpired())
                                    <span class="bg-red-100 text-red-800 text-xs font-semibold px-2 py-1 rounded">Expired</span>
                                @endif
                            </div>
                            <p class="text-gray-600 text-sm">
                                Expires {{ str_pad($method->card_exp_month, 2, '0', STR_PAD_LEFT) }}/{{ $method->card_exp_year }}
                            </p>
                            <p class="text-gray-500 text-xs">Added {{ $method->created_at->diffForHumans() }}</p>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center space-x-2">
                        @if(!$method->is_default)
                            <form action="{{ route('payment_methods.set_default', $method->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 transition text-sm">
                                    Set as Default
                                </button>
                            </form>
                        @endif

                        <form action="{{ route('payment_methods.destroy', $method->id) }}" method="POST"
                              onsubmit="return confirm('Are you sure you want to remove this payment method?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 transition text-sm">
                                <i class="fas fa-trash"></i> Remove
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h3 class="font-semibold text-blue-900 mb-2"><i class="fas fa-info-circle"></i> About Saved Payment Methods</h3>
            <ul class="text-sm text-blue-800 space-y-1">
                <li>• Your payment information is securely stored by Stripe</li>
                <li>• Use saved cards for faster checkout</li>
                <li>• Set a default card to auto-select during checkout</li>
                <li>• You can remove cards at any time</li>
            </ul>
        </div>
    @endif
</div>
@endsection
