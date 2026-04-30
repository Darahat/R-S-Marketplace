

<ul class="max-h-64 overflow-y-auto divide-y">
    <template x-if="cartItems.length === 0">
        <li class="p-8 text-center text-gray-500 text-sm">Your cart is empty</li>
    </template>
 <template x-for="item in cartItems" :key="item.id">
            <li class="flex items-center justify-between p-3">
                <div class="flex items-center">
                    <img :src="item.image" class="w-10 h-10 rounded mr-3" alt="Product">
                    <div>
                        <p class="text-sm font-medium" x-text="item.name"></p>
                        <p class="text-xs text-gray-500" x-text="item.quantity + ' x ' +
    formatPrice(item.price)"></p>
                    </div>
                </div>
                <div class="text-sm font-semibold text-gray-800" x-text="formatPrice(item.price *
    item.quantity)"></div>
            </li>
</template>
</ul>

<div class="p-4 border-t">
    <div class="flex justify-between text-sm text-gray-700 mb-3">
        <span>Total</span>
<span x-text="formatPrice(totalPrice)">৳{{ $totalPriceAmount ?? '0.00' }}</span>    </div>
    <a href="{{ route('cart.view') }}"
        class="block w-full text-center bg-primary hover:bg-secondary text-white font-medium py-2 px-4 rounded-md transition-all duration-300 transform hover:scale-[1.02] shadow-md hover:shadow-lg">
        <div class="flex items-center justify-center space-x-2">
            <i class="fas fa-shopping-cart"></i>
            <span>View Cart</span>
            <span class="ml-1 bg-white text-primary text-xs font-semibold rounded-full px-2 py-1" x-text="totalItemCount + ' items'"></span>
        </div>
    </a>
</div>


