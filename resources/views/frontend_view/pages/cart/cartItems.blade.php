@if(count($cartItems) > 0)
            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <!-- Cart Items -->
                <div class="divide-y divide-gray-200">
                    @foreach($cartItems as $item)
                    <div class="p-4 flex items-center hover:bg-gray-50">
                        <img src="{{ asset($item['image']) }}" alt="{{ $item['name'] }}" class="w-20 h-20 object-cover rounded">
                        
                        <div class="ml-4 flex-1">
                            <h3 class="font-medium text-lg">{{ $item['name'] }}</h3>
                            <p class="text-gray-600">${{ number_format($item['price'], 2) }}</p>
                        </div>
                        
                        <div class="flex items-center space-x-1">
                            <button type="button"
                                class="decrement bg-gray-200 px-2 py-1 text-sm rounded disabled:opacity-50 disabled:cursor-not-allowed"
                                data-id="{{ $item['id'] }}">
                                −
                            </button>

                            <input 
                                type="number" 
                                class="cart-qty-input w-16 border rounded px-2 py-1 text-center"
                                data-id="{{ $item['id'] }}" 
                                value="{{ $item['quantity'] }}" 
                                min="1"
                                data-original-value="{{ $item['quantity'] }}">

                            <button type="button"
                                class="increment bg-gray-200 px-2 py-1 text-sm rounded"
                                data-id="{{ $item['id'] }}">
                                +
                            </button>
                        </div>


                        <div class="flex items-center">
                            
                            
                            <form action="{{ route('cart.remove') }}" method="POST" class="ml-4">
                                @csrf
                                <button class="remove-cart-item text-red-500 text-xs ml-2" data-id="{{ $item['id'] }}">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <!-- Cart Summary -->
                <div class="p-4 bg-gray-50 border-t">
                    <div class="flex justify-between items-center">
                        <span class="font-medium">Total:</span>
                        <span class="font-bold text-xl">${{ number_format($total, 2) }}</span>
                    </div>
                    
                    <div class="mt-4 flex justify-end space-x-4">
                        <a href="{{ route('home') }}" 
                           class="px-4 py-2 border border-primary text-primary rounded hover:bg-gray-100">
                            Continue Shopping
                        </a>
                        <a href="{{ route('checkout') }}" 
                           class="px-6 py-2 bg-primary text-white rounded hover:bg-secondary">
                            Proceed to Checkout
                        </a>
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-12">
                <i class="fas fa-shopping-cart text-5xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-medium text-gray-500">Your cart is empty</h3>
                <a href="{{ route('home') }}" class="mt-4 inline-block px-6 py-2 bg-primary text-white rounded hover:bg-secondary">
                    Start Shopping
                </a>
            </div>
        @endif

        @push('scripts')
<script>
    function updateCartQty(itemId, quantity, inputField) {
        inputField.prop('disabled', true);

        $.ajax({
            url: "{{ route('cart.update') }}",
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                itemId: itemId,
                quantity: quantity
            },
            success: function (response) {
                $('.font-bold.text-xl').text('$' + response.total.toFixed(2));
                $('#cart-count').text(response.totalQuantity);

                $.get("{{ route('cart.view.refresh') }}", function (data) {
                    $('#cart-view-section').html(data);
                });

                $.get("{{ route('cart.refresh') }}", function(data) {
                    $('#cart-dropdown').html(data);
                });

                if (typeof toastr !== 'undefined') {
                    toastr.success(response.message);
                }
            },
            error: function () {
                if (typeof toastr !== 'undefined') {
                    toastr.error('Error updating cart');
                }
                inputField.val(inputField.data('original-value'));
            },
            complete: function () {
                inputField.prop('disabled', false);
                toggleDecrementButton(inputField);
            }
        });
    }

    function toggleDecrementButton(input) {
        const qty = parseInt(input.val());
        const decrementBtn = input.siblings('.decrement');
        if (qty <= 1) {
            decrementBtn.prop('disabled', true);
        } else {
            decrementBtn.prop('disabled', false);
        }
    }

    $(document).on('click', '.increment, .decrement', function () {
        let isIncrement = $(this).hasClass('increment');
        let input = $(this).siblings('.cart-qty-input');
        let currentQty = parseInt(input.val());
        let newQty = isIncrement ? currentQty + 1 : Math.max(1, currentQty - 1);
        input.val(newQty);

        let itemId = $(this).data('id');
        updateCartQty(itemId, newQty, input);
    });

    $(document).on('change', '.cart-qty-input', function () {
        let input = $(this);
        let itemId = input.data('id');
        let newQty = parseInt(input.val()) || 1;

        if (newQty < 1) {
            newQty = 1;
            input.val(newQty);
        }

        updateCartQty(itemId, newQty, input);
    });

    // Initial setup on page load
    $(document).ready(function () {
        $('.cart-qty-input').each(function () {
            toggleDecrementButton($(this));
        });
    });
</script>
@endpush

