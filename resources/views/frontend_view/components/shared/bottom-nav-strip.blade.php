<!-- Bottom Nav Strip (Desktop only) -->
<div class="hidden md:block border-t border-gray-100">
    <div class="container mx-auto px-4">
        <nav class="flex items-center gap-1 py-1.5">
            <a href="{{ url('/') }}" class="px-3 py-1.5 text-sm text-gray-600 hover:text-primary font-medium hover:bg-primary/5 rounded transition-colors">
                <i class="fas fa-home mr-1"></i>Home
            </a>
            <a href="{{ url('/#todays-deals') }}" class="px-3 py-1.5 text-sm text-gray-600 hover:text-primary font-medium hover:bg-primary/5 rounded transition-colors">
                <i class="fas fa-fire mr-1 text-orange-500"></i>Today's Deals
            </a>
            <a href="{{ url('/#flash-sale') }}" class="px-3 py-1.5 text-sm text-gray-600 hover:text-primary font-medium hover:bg-primary/5 rounded transition-colors">
                <i class="fas fa-bolt mr-1 text-yellow-500"></i>Flash Sale
            </a>
            <a href="{{ url('/#best-selling') }}" class="px-3 py-1.5 text-sm text-gray-600 hover:text-primary font-medium hover:bg-primary/5 rounded transition-colors">
                <i class="fas fa-star mr-1 text-yellow-400"></i>Best Sellers
            </a>
            @auth
                <a href="{{ route('customer.orders') }}" class="px-3 py-1.5 text-sm text-gray-600 hover:text-primary font-medium hover:bg-primary/5 rounded transition-colors">
                    <i class="fas fa-truck mr-1"></i>Track Order
                </a>
            @else
                <button type="button" @click="openLoginModal()" class="px-3 py-1.5 text-sm text-gray-600 hover:text-primary font-medium hover:bg-primary/5 rounded transition-colors">
                    <i class="fas fa-truck mr-1"></i>Track Order
                </button>
            @endauth
            <a href="{{ route('support') }}" class="px-3 py-1.5 text-sm text-gray-600 hover:text-primary font-medium hover:bg-primary/5 rounded transition-colors">
                <i class="fas fa-headset mr-1"></i>Support
            </a>
        </nav>
    </div>
</div>
