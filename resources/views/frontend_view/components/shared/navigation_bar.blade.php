<!-- Header -->
<header class="bg-white text-gray-800 sticky top-0 z-50 shadow-sm">
    <!-- Top Menu Bar -->
    <div class="bg-primary text-white">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between py-2 text-xs">
                <!-- Left side menu items -->
                <div class="flex items-center space-x-4">
                    <a href="#" class="hover:text-success transition">Seller Center</a>
                    <a href="#" class="hover:text-success transition">Download App</a>
                    <span class="hidden md:inline">|</span>
                    <a href="#" class="hidden md:inline hover:text-success transition">Donates</a>
                    <span class="hidden md:inline">|</span>
                    <a href="#" class="hover:text-success transition text-2xl text-yellow-600">Demo Site</a>

                </div>
                
                <!-- Right side menu items -->
                <div class="flex items-center space-x-4" >
                    <a href="#" class="hover:text-success transition flex items-center">
                        <i class="fas fa-question-circle mr-1"></i>
                        <span class="hidden sm:inline">Help & Support</span>
                    </a>
                    <span @if(Auth::check()) style="display: none;" @endif class="hidden sm:inline">|</span>

                    <div @if(Auth::check()) style="display: none;" @endif>
               
                        <button href="#" data-modal="login" class="hover:text-success transition">Logins</button>
                        <span>/</span>
                        <button href="#" data-modal="register" class="hover:text-success transition">Register</button>
                    </div>
                  
                </div>
            </div>
        </div>
    </div>
    
    <!-- Main Navigation with Search -->
    <div class="container mx-auto px-4 py-3">
        <div class="flex flex-col">
            <!-- Logo and Search Row -->
            <div class="flex flex-col md:flex-row items-center justify-between mb-4 gap-4">
                <!-- Logo -->
                <div class="flex-shrink-0">
                    <a href="{{route('home')}}" class="flex items-center">
                        <img src="{{asset('/images/logo/logo.png')}}" alt="MarketGhor" class="h-10">
                    </a>
                </div>
                
                <!-- Main Search -->
                <div class="flex-grow max-w-2xl w-full">
                    <div class="flex">
                        <input type="text" placeholder="Search in MarketGhor..." 
                               class="w-full py-2.5 px-4 border-2 border-primary rounded-l-md focus:outline-none focus:border-success" />
                
                        {{-- <select class="bg-gray-100 border-2 border-l-0 border-primary text-gray-700 py-2.5 px-2 focus:outline-none">
                            <option>All Categories</option>
                            @foreach($categories as $category)
                                <option>{{ $category->name }}</option>
                            @endforeach
                        </select> --}}
                
                        <button class="bg-primary hover:bg-secondary text-white py-2.5 px-5 rounded-r-md transition flex items-center">
                            <i class="fas fa-search"></i>
                            <span class="hidden sm:inline ml-1">Search</span>
                        </button>
                    </div>
                </div>
                
                
                <!-- Cart and Account -->
                <div class="flex-shrink-0 flex items-center space-x-4">
                <a href="#" class="relative flex flex-col items-center text-gray-700 hover:text-primary transition">
                    <i class="fas fa-heart text-xl"></i>
                    <span class="text-xs mt-1 hidden sm:block">Wishlist</span>

                    <!-- Count Badge -->
                    <span id="wishlist-count"
                        class="absolute -top-2 -right-2 bg-red-500 text-white text-xs w-5 h-5 flex items-center justify-center rounded-full">
                        {{ session('wishlist') ? count(session('wishlist')) : 0 }}
                    </span>
                </a>

                    <div x-data="{ openCart: false }" class="relative">
                        <!-- Cart Button -->
                        <a href="javascript:void(0)" @click="openCart = !openCart" class="flex flex-col items-center text-gray-700 hover:text-primary transition relative">
                            <i class="fas fa-shopping-cart text-xl"></i>
                            <span class="text-xs mt-1 hidden sm:block">Cart</span>
                            <span class="absolute -top-2 -right-2 bg-primary text-white text-xs rounded-full h-5 w-5 flex items-center justify-center" id="cart-count">{{ collect(session('cart', []))->sum('quantity') }}</span>
                        </a>
                   
                        <!-- Dropdown Cart -->
                        <div x-show="openCart" @click.away="openCart = false" x-transition 
                             class="absolute right-0 mt-2 w-80 bg-white shadow-xl rounded-lg border border-gray-200 z-50" id="cart-dropdown">
                            
                            <div class="p-4 border-b">
                                <h3 class="font-semibold text-gray-800">Your Cart</h3>
                            </div>
                            <!-- Cart Items -->
                            @include('frontend_view.components.cards.cartDropdown')
                            
                        </div>
                    </div>
                    
                    <!-- Account dropdown -->
<div class="relative" x-data="{ open: false }">
    @if(Auth::check())
        <button @click="open = !open" class="flex flex-col items-center text-gray-700 hover:text-primary transition focus:outline-none">
            <img class="h-8 w-8 rounded-full object-cover profile-photo-preview" src="{{ Auth::user()->profile_photo ? asset(Auth::user()->profile_photo) : asset('images/default-avatar.png') }}" alt="User Avatar">
            <span class="text-xs mt-1 hidden sm:block">Account</span>
        </button>

        <!-- Dropdown -->
        <div x-show="open" @click.outside="open = false" x-transition
             class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-md shadow-lg z-50">
            <a href="{{ route('customer.profile_setting') }}"     
               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" target="_blank">Account Settings</a>
            <a href="#"
               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100" target="_blank">My Orders</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                    Logout
                </button>
            </form>
        </div>
    @else
        {{-- <a href="#" class="flex flex-col items-center text-gray-700 hover:text-primary transition">
            <i class="fas fa-user-circle text-xl"></i>
            <span class="text-xs mt-1 hidden sm:block">Account</span>
        </a> --}}
    @endif
</div>

                </div>
            </div>
            
            <!-- Category Navigation -->
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
    
                <!-- All Categories Button -->
                <div class="relative group">
                    <button class="bg-primary text-white px-4 py-2 rounded-md flex items-center hover:bg-secondary transition">
                        <i class="fas fa-bars mr-2"></i>
                        <span>All Categories</span>
                        <i class="fas fa-chevron-down ml-2 text-xs"></i>
                    </button>
                    
                    <!-- Mega Menu Dropdown -->
                    <div class="absolute left-0 top-full mt-1 w-64 bg-white shadow-xl rounded-md opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 border border-gray-200">
                        <ul class="py-2">
                            @foreach($categories as $category)
                            <li class="relative group/sub border-b border-gray-100 last:border-0">
                                <a href="{{route('category',$category->slug)}}" class="px-4 py-3 hover:bg-gray-50 flex items-center justify-between text-gray-700">
                                    <div class="flex items-center">
                                        <i class="fas fa-{{ $category->icon ?? 'tag' }} mr-3 text-primary"></i>
                                        <span>{{ $category->name }}</span>
                                    </div>
                                    @if($category->subcategories->isNotEmpty())
                                    <i class="fas fa-chevron-right text-xs text-gray-400"></i>
                                    @endif
                                </a>
            
                                @if($category->subcategories->isNotEmpty())
                                <!-- Subcategory Dropdown -->
                                <ul class="absolute top-0 left-full mt-0 ml-1 w-60 bg-white shadow-md rounded-md opacity-0 invisible group-hover/sub:opacity-100 group-hover/sub:visible transition-all duration-200 border border-gray-200 z-50">
                                    @foreach($category->subcategories as $subcategory)
                                    <li class="border-b border-gray-100 last:border-0">
                                        <a href="{{route('category',$subcategory->slug)}}" class="block px-4 py-2 hover:bg-gray-50 text-gray-700">
                                            {{ $subcategory->name }}
                                        </a>
                                    </li>
                                    @endforeach
                                </ul>
                                @endif
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            
                <!-- Main Navigation Links -->
                <nav class="flex-1 flex items-center flex-wrap gap-3 md:gap-6">
                    <a href="{{url('/')}}" class="text-gray-700 hover:text-primary font-medium transition">Home</a>
                    <a href="#" class="text-gray-700 hover:text-primary font-medium transition">Today's Deals</a>
                    <a href="#" class="text-gray-700 hover:text-primary font-medium transition">Flash Sale</a>
                    <a href="#" class="text-gray-700 hover:text-primary font-medium transition">Track Order</a>
                    <a href="#" class="text-gray-700 hover:text-primary font-medium transition">Customer Service</a>
                </nav>
            </div>
            
        </div>
    </div>
</header>

<!-- Mobile Search Bar (Fixed at bottom on mobile) -->
<div class="fixed bottom-0 left-0 right-0 bg-white shadow-lg border-t border-gray-200 p-2 z-40 md:hidden">
    <div class="flex items-center">
        <input type="text" placeholder="Search products..." class="flex-grow py-2 px-3 border border-gray-300 rounded-l-md focus:outline-none focus:border-primary">
        <button class="bg-primary text-white py-2 px-4 rounded-r-md">
            <i class="fas fa-search"></i>
        </button>
    </div>
</div>
{{-- Login Modal --}}
<div id="loginModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden justify-center items-center">
    <div class="bg-white rounded-lg w-full max-w-md p-6 relative">
        <button id="closeLoginModal" class="absolute top-2 right-2 text-gray-400 hover:text-red-500">
            <i class="fas fa-times"></i>
        </button>
        @include('frontend_view.pages.auth.login')
    </div>
</div>

{{-- Register Modal --}}
<div id="registerModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden justify-center items-center">
    <div class="bg-white rounded-lg w-full max-w-md p-6 relative">
        <button id="closeRegisterModal" class="absolute top-2 right-2 text-gray-400 hover:text-red-500">
            <i class="fas fa-times"></i>
        </button>
        @include('frontend_view.pages.auth.register')
    </div>
</div>
@push('scripts')
<script>
    // Mobile menu functionality would go here
    document.addEventListener('DOMContentLoaded', function() {
        // Handle category hover on mobile
        const categoryButtons = document.querySelectorAll('[data-category-toggle]');
        categoryButtons.forEach(button => {
            button.addEventListener('click', function() {
                const submenu = this.nextElementSibling;
                submenu.classList.toggle('hidden');
            });
        });
    });
  
    $(document).ready(function () {
        // Show modals
        $('[data-modal="login"]').on('click', function (e) {
            e.preventDefault();
            $('#loginModal').removeClass('hidden').addClass('flex');
        });

        $('[data-modal="register"]').on('click', function (e) {
            e.preventDefault();
            $('#registerModal').removeClass('hidden').addClass('flex');
        });

        // Close modals
        $('#closeLoginModal').on('click', function () {
            $('#loginModal').addClass('hidden').removeClass('flex');
        });

        $('#closeRegisterModal').on('click', function () {
            $('#registerModal').addClass('hidden').removeClass('flex');
        });
    });
</script>
@endpush

 