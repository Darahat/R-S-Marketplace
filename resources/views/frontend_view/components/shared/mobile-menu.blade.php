<!-- Mobile Menu Component -->
<div
    x-show="mobileMenuOpen"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0 -translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 -translate-y-2"
    class="md:hidden border-t border-gray-100 bg-white shadow-lg"
    style="display: none;"
>
    <nav class="container mx-auto px-4 py-3 space-y-0.5">
        <a href="{{ url('/') }}" class="flex items-center px-3 py-3 text-gray-700 hover:bg-primary hover:text-white rounded-lg transition-colors font-medium text-sm">
            <i class="fas fa-home mr-3 w-5 text-center"></i>Home
        </a>
        <a href="{{ url('/#todays-deals') }}" class="flex items-center px-3 py-3 text-gray-700 hover:bg-primary hover:text-white rounded-lg transition-colors font-medium text-sm">
            <i class="fas fa-fire mr-3 w-5 text-center text-orange-500"></i>Today's Deals
        </a>
        <a href="{{ url('/#flash-sale') }}" class="flex items-center px-3 py-3 text-gray-700 hover:bg-primary hover:text-white rounded-lg transition-colors font-medium text-sm">
            <i class="fas fa-bolt mr-3 w-5 text-center text-yellow-500"></i>Flash Sale
        </a>
        <a href="{{ url('/#best-selling') }}" class="flex items-center px-3 py-3 text-gray-700 hover:bg-primary hover:text-white rounded-lg transition-colors font-medium text-sm">
            <i class="fas fa-star mr-3 w-5 text-center text-yellow-400"></i>Best Sellers
        </a>
        @auth
            <a href="{{ route('customer.orders') }}" class="flex items-center px-3 py-3 text-gray-700 hover:bg-primary hover:text-white rounded-lg transition-colors font-medium text-sm">
                <i class="fas fa-truck mr-3 w-5 text-center"></i>Track Order
            </a>
            <a href="{{ route('customer.profile') }}" class="flex items-center px-3 py-3 text-gray-700 hover:bg-primary hover:text-white rounded-lg transition-colors font-medium text-sm">
                <i class="fas fa-user mr-3 w-5 text-center"></i>My Profile
            </a>
            <div class="my-2 h-px bg-gray-100"></div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center px-3 py-3 text-red-600 hover:bg-red-50 rounded-lg transition-colors font-medium text-sm">
                    <i class="fas fa-sign-out-alt mr-3 w-5 text-center"></i>Logout
                </button>
            </form>
        @else
            <div class="my-2 h-px bg-gray-100"></div>
            <button type="button" @click="openLoginModal()" class="w-full flex items-center px-3 py-3 text-gray-700 hover:bg-primary hover:text-white rounded-lg transition-colors font-medium text-sm">
                <i class="fas fa-sign-in-alt mr-3 w-5 text-center"></i>Login
            </button>
            <button type="button" @click="openRegisterModal()" class="w-full flex items-center px-3 py-3 text-gray-700 hover:bg-primary hover:text-white rounded-lg transition-colors font-medium text-sm">
                <i class="fas fa-user-plus mr-3 w-5 text-center"></i>Sign Up
            </button>
        @endauth
    </nav>
</div>
