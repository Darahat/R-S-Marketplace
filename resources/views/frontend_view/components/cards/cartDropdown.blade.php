@php
    $cart = $cart ?? [];
    $totalPriceAmount = $totalPriceAmount ?? 0;
    $totalItemCount = $totalItemCount ?? 0;

@endphp

<ul class="max-h-64 overflow-y-auto divide-y">
    @if(count($cart) === 0)
        <li class="p-4 text-sm text-gray-500 text-center">Your cart is empty</li>
    @endif
    @foreach($cart as $item)
        @php
            $itemTotal = $item['price'] * $item['quantity'];
            $totalPriceAmount += $itemTotal;
            $totalItemCount += $item['quantity'];
        @endphp
        <li class="flex items-center justify-between p-3" data-product-id="{{ $item['id'] }}">
            <div class="flex items-center">
                <img src="{{ $item['image'] }}" class="w-10 h-10 rounded mr-3" alt="Product">
                <div>
                    <p class="text-sm font-medium item-name">{{ \Illuminate\Support\Str::limit($item['name'], 20) }}</p>
                    <p class="text-xs text-gray-500 item-meta">{{ $item['quantity'] }} x {{ number_format($item['price'], 2) }}</p>
                </div>
            </div>
            <div class="text-sm font-semibold text-gray-800 item-total">{{ number_format($itemTotal, 2) }}</div>
        </li>
    @endforeach
</ul>

<div class="p-4 border-t">
    <div class="flex justify-between text-sm text-gray-700 mb-3">
        <span>Total</span>
        <span class="font-semibold">৳{{ number_format($totalPriceAmount, 2) }}</span>
    </div>
    <a href="{{ route('cart.view') }}"
        class="block w-full text-center bg-primary hover:bg-secondary text-white font-medium py-2 px-4 rounded-md transition-all duration-300 transform hover:scale-[1.02] shadow-md hover:shadow-lg">
        <div class="flex items-center justify-center space-x-2">
            <i class="fas fa-shopping-cart"></i>
            <span>View Cart</span>
            <span class="ml-1 bg-white text-primary text-xs font-semibold rounded-full px-2 py-1">{{ $totalItemCount }} items</span>
        </div>
    </a>
</div>


