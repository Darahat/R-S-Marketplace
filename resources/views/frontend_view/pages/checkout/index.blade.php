@extends('frontend_view.layouts.home')

@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Checkout</h1>
    
    @if ($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    
    <div class="flex flex-col md:flex-row gap-8">
    <!-- Shipping Information -->
    <div class="md:w-2/3">
        @auth
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-xl font-semibold mb-4">Shipping Information</h2>
            
            
                <!-- Show address form for logged-in users -->
                <form id="checkout-form" action="{{ route('checkout.process') }}" method="POST">
                    @csrf
                    
                    <!-- Load saved address if available -->
                    @php
                        $user = Auth::user();
                       
                        $address = DB::table('addresses')
                            ->leftJoin('districts', 'addresses.district_id', '=', 'districts.id')
                            ->leftJoin('upazilas', 'addresses.upazila_id', '=', 'upazilas.id')
                            ->leftJoin('unions', 'addresses.union_id', '=', 'unions.id')
                            ->where('addresses.address_type', 'shipping')
                            ->where('addresses.user_id', $user->id)
                            ->where('is_default', true)
                            ->orderBy('addresses.is_default', 'desc')
                            ->select(
                                'addresses.*',
                                'districts.name as district_name',
                                'upazilas.name as upazila_name',
                                'unions.name as union_name'
                            )
                            ->first();
                    @endphp
                     <h3 class="font-medium text-gray-900">{{ $address->full_name }}</h3>
                    <p class="text-sm text-gray-600">{{ $address->street_address }}</p>
                    <p class="text-sm text-gray-600">{{ $address->union_name }}, {{ $address->upazila_name }}, {{ $address->district_name }}-{{ $address->postal_code }}</p>
                    <p class="text-sm text-gray-600">{{ $address->country }}</p>
                    <p class="text-sm text-gray-600 mt-2"><i class="fas fa-phone mr-1"></i> {{ $address->phone }}</p>

                    <div class="mb-6 mt-4">
                        <label for="notes" class="block text-sm font-medium text-gray-700">Order Notes (Optional)</label>
                        <textarea id="notes" name="notes" rows="3" 
                                  class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-primary focus:border-primary">{{ old('notes') }}</textarea>
                    </div>
                    
                    <h2 class="text-xl font-semibold mb-4">Payment Method *</h2>
                    <div class="space-y-2 mb-6">
                        <div class="flex items-center">
                            <input id="cash" name="payment_method" type="radio" value="cash" 
                                   class="h-4 w-4 text-primary focus:ring-primary border-gray-300" 
                                   {{ old('payment_method', 'cash') === 'cash' ? 'checked' : '' }}>
                            <label for="cash" class="ml-3 block text-sm font-medium text-gray-700">
                                Cash on Delivery
                            </label>
                        </div>
                        <div class="flex items-center">
                            <input id="credit_card" name="payment_method" type="radio" value="credit_card" 
                                   class="h-4 w-4 text-primary focus:ring-primary border-gray-300" 
                                   {{ old('payment_method', 'credit_card') === 'credit_card' ? 'checked' : '' }}>
                            <label for="credit_card" class="ml-3 block text-sm font-medium text-gray-700">
                                Credit Card
                            </label>
                        </div>
                        
                    </div>
                    
                    <!-- Stripe Elements Container -->
                    <div id="card-element" class="mb-4 {{ old('payment_method', 'credit_card') !== 'credit_card' ? 'hidden' : '' }}">
                        <!-- Stripe card element will be inserted here -->
                    </div>
                    
                    <!-- Used to display form errors -->
                    <div id="card-errors" role="alert" class="text-red-500 text-sm mb-4"></div>
                    
                    <input type="hidden" name="stripeToken" id="stripeToken">
                    
                   
                    
                   
                    
                   
                    
                    
                    
                    <!-- Rest of your form... -->
                    
                </form>
            @else
                <!-- Show login prompt for guests -->
                <div class="text-center py-8">
                    <h3 class="text-lg font-medium mb-4">Please login to continue checkout</h3>
                    <button  data-modal="login" 
                            class="bg-primary hover:bg-secondary text-white py-2 px-6 rounded-md font-medium transition duration-200">
                        Login / Register
                    </button>
                     
                </div>
            @endauth
        </div>
    </div>
    
    <!-- Order Summary -->
      <div class="md:w-1/3">
            <div class="bg-white rounded-lg shadow-md p-6 sticky top-4">
                <h2 class="text-xl font-semibold mb-4">Order Summary</h2>
                
                <div class="divide-y divide-gray-200">
                    @foreach($cartItems as $item)
                    <div class="py-4 flex justify-between">
                        <div class="flex items-center">
                            <img src="{{ asset($item['image']) }}" alt="{{ $item['name'] }}" class="w-16 h-16 object-cover rounded">
                            <div class="ml-3">
                                <h3 class="text-sm font-medium">{{ $item['name'] }}</h3>
                                <p class="text-xs text-gray-500">Qty: {{ $item['quantity'] }}</p>
                            </div>
                        </div>
                        <div class="text-sm font-medium">
                            ${{ number_format($item['price'] * $item['quantity'], 2) }}
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <div class="border-t border-gray-200 mt-4 pt-4">
                    <div class="flex justify-between mb-2">
                        <span class="text-sm text-gray-600">Subtotal</span>
                        <span class="text-sm font-medium">${{ number_format($total, 2) }}</span>
                    </div>
                    <div class="flex justify-between mb-2">
                        <span class="text-sm text-gray-600">Shipping</span>
                        <span class="text-sm font-medium">$0.00</span>
                    </div>
                    <div class="flex justify-between text-lg font-bold mt-4">
                        <span>Total</span>
                        <span>${{ number_format($total, 2) }}</span>
                    </div>
                </div>
                
                <button type="submit" form="checkout-form" id="submit-button"
                        class="w-full mt-6 bg-primary hover:bg-secondary text-white py-2 px-4 rounded-md font-medium transition duration-200">
                    Place Order
                </button>
            </div>
</div>


</div>
@endsection

@push('scripts')
<script src="https://js.stripe.com/v3/"></script>
<script>
    // Stripe elements initialization
    const stripe = Stripe('{{ config('services.stripe.key') }}');
    const elements = stripe.elements();
    const cardElement = elements.create('card');
    
    // Only mount card element if credit card is selected
    @if(old('payment_method', 'credit_card') === 'credit_card')
        cardElement.mount('#card-element');
    @endif
    
    // Handle payment method change
    document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'credit_card') {
                document.getElementById('card-element').classList.remove('hidden');
                cardElement.mount('#card-element');
            } else {
                document.getElementById('card-element').classList.add('hidden');
                cardElement.unmount();
            }
        });
    });
    
    // Handle form submission
    const form = document.getElementById('checkout-form');
    const submitButton = document.getElementById('submit-button');
    
    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        
        const paymentMethod = document.querySelector('input[name="payment_method"]:checked').value;
        
        if (paymentMethod === 'credit_card') {
            submitButton.disabled = true;
            
            const {token, error} = await stripe.createToken(cardElement);
            
            if (error) {
                const errorElement = document.getElementById('card-errors');
                errorElement.textContent = error.message;
                submitButton.disabled = false;
            } else {
                document.getElementById('stripeToken').value = token.id;
                form.submit();
            }
        } else {
            form.submit();
        }
    });
    
    // Display card errors
    cardElement.on('change', function(event) {
        const displayError = document.getElementById('card-errors');
        if (event.error) {
            displayError.textContent = event.error.message;
        } else {
            displayError.textContent = '';
        }
    });
</script>
@endpush