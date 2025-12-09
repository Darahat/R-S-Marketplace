@php
    $cart = session('cart', []);
    $totalPriceAmount = 0;
    $totalItemCount = 0;
@endphp                                

<ul class="max-h-64 overflow-y-auto divide-y">
    @foreach($cart as $item)
        @php 
            $itemTotal = $item['price'] * $item['quantity'];
            $totalPriceAmount += $itemTotal;
            $totalItemCount += $item['quantity'];
        @endphp
        <li class="flex items-center justify-between p-3">
            <div class="flex items-center">
                <img src="{{ $item['image'] }}" class="w-10 h-10 rounded mr-3" alt="Product">
                <div>
                    <p class="text-sm font-medium">{{ \Illuminate\Support\Str::limit($item['name'], 20) }}</p>
                    <p class="text-xs text-gray-500">{{ $item['quantity'] }} x {{ number_format($item['price'], 2) }}</p>
                </div>
            </div>
            <div class="text-sm font-semibold text-gray-800">{{ number_format($itemTotal, 2) }}</div>
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
            <span class="ml-1 bg-white bg-opacity-20 text-xs rounded-full px-2 py-1">{{ $totalItemCount }} items</span>
        </div>
    </a>
</div>