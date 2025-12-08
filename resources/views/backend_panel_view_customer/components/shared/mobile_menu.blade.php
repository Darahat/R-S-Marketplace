<!-- Mobile Menu -->
<div x-show="mobileMenuOpen" class="md:hidden fixed inset-0 z-40">
    <div class="fixed inset-0 bg-gray-600 bg-opacity-75" @click="mobileMenuOpen = false"></div>
    
    <div class="fixed inset-y-0 left-0 w-64 bg-gradient-to-b from-primary to-secondary shadow-lg">
        <div class="flex items-center justify-between h-16 px-4 border-b border-white border-opacity-20">
            <span class="text-white text-xl font-semibold">MarketGhor</span>
            <button @click="mobileMenuOpen = false" class="text-white focus:outline-none">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <div class="px-4 py-4">
            <!-- User Profile -->
            <div class="flex items-center px-4 py-3 mb-4 bg-white bg-opacity-10 rounded-lg">
                <img class="h-10 w-10 rounded-full object-cover" src="{{ Auth::user()->avatar ?? asset('images/default-avatar.png') }}" alt="User Avatar">
                <div class="ml-3">
                    <p class="text-sm font-medium text-white">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-white text-opacity-70">{{ Auth::user()->email }}</p>
                </div>
            </div>
            
            <!-- Mobile Navigation Links -->
            <nav class="space-y-1">
                <a href="{{ route('customer.dashboard') }}" 
                   class="flex items-center px-4 py-3 text-sm font-medium text-white rounded-lg hover:bg-white hover:bg-opacity-10 transition">
                    <i class="fas fa-tachometer-alt mr-3"></i>
                    Dashboard
                </a>
{{--                 
                <a href="{{ route('customer.orders') }}" 
                   class="flex items-center px-4 py-3 text-sm font-medium text-white rounded-lg hover:bg-white hover:bg-opacity-10 transition">
                    <i class="fas fa-shopping-bag mr-3"></i>
                    My Orders
                </a>
                
                <a href="{{ route('customer.wishlist') }}" 
                   class="flex items-center px-4 py-3 text-sm font-medium text-white rounded-lg hover:bg-white hover:bg-opacity-10 transition">
                    <i class="fas fa-heart mr-3"></i>
                    Wishlist
                </a>
                
                <a href="{{ route('customer.profile') }}" 
                   class="flex items-center px-4 py-3 text-sm font-medium text-white rounded-lg hover:bg-white hover:bg-opacity-10 transition">
                    <i class="fas fa-user mr-3"></i>
                    Profile
                </a>
                
                <a href="{{ route('customer.profile_setting') }}" 
                   class="flex items-center px-4 py-3 text-sm font-medium text-white rounded-lg hover:bg-white hover:bg-opacity-10 transition">
                    <i class="fas fa-cog mr-3"></i>
                    Settings
                </a> --}}
                
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center px-4 py-3 text-sm font-medium text-white rounded-lg hover:bg-white hover:bg-opacity-10 transition">
                        <i class="fas fa-sign-out-alt mr-3"></i>
                        Logout
                    </button>
                </form>
            </nav>
        </div>
    </div>
</div>