<!-- Enhanced Responsive Header with Mobile Menu -->
<header class="bg-white shadow-lg sticky top-0 z-50 transition-all duration-300" x-data="{ mobileMenuOpen: false, searchOpen: false, scrolled: false }"
        @scroll.window="scrolled = (window.pageYOffset > 10)">
    <!-- Top Bar -->
    <div class="bg-gradient-to-r from-primary via-primary to-secondary text-white hidden sm:block">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between py-2 text-xs md:text-sm">
                <!-- Left side -->
                <div class="flex items-center space-x-4 md:space-x-6">
                    <a href="#" class="flex items-center hover:text-yellow-300 transition duration-200">
                        <i class="fas fa-mobile-alt mr-1.5"></i>
                        <span class="hidden sm:inline">Download App</span>
                    </a>
                    <a href="#" class="flex items-center hover:text-yellow-300 transition duration-200">
                        <i class="fas fa-headset mr-1.5"></i>
                        <span class="hidden md:inline">24/7 Support</span>
                    </a>
                </div>

                <!-- Right side -->
                <div class="flex items-center space-x-3 md:space-x-5">
                    @guest
                        <div class="flex items-center space-x-2 md:space-x-3">
                            <button data-modal="login" class="hover:text-yellow-300 transition duration-200 font-medium">Login</button>
                            <span class="text-white/50">/</span>
                            <button data-modal="register" class="hover:text-yellow-300 transition duration-200 font-medium">Register</button>
                        </div>
                    @else
                        <span class="text-sm">Welcome, {{ Auth::user()->name }}</span>
                    @endguest
                </div>
            </div>
        </div>
    </div>

    <!-- Main Navigation -->
    <div class="container mx-auto px-3 sm:px-4 py-2 md:py-3">
        <div class="flex items-center justify-between gap-3 md:gap-4">
            <!-- Logo -->
            <div class="flex-shrink-0 transition-transform duration-200 hover:scale-105">
                <a href="{{route('home')}}" class="flex items-center">
                    <img src="{{asset('/images/logo/logo.png')}}" alt="MarketGhor" class="h-10 sm:h-12 md:h-14 lg:h-16">
                </a>
            </div>

            <!-- Desktop Search Bar -->
            <div class="hidden lg:flex flex-1 max-w-2xl">
                <form action="{{ route('search') ?? '#' }}" method="GET" class="flex w-full rounded-lg overflow-hidden shadow-md hover:shadow-lg transition-shadow">
                    <input
                        type="text"
                        name="q"
                        placeholder="Search products, brands, and more..."
                        class="flex-1 py-2.5 px-4 border-0 focus:outline-none focus:ring-2 focus:ring-primary transition"
                        required
                    />
                    <button type="submit" class="bg-primary hover:bg-primary-dark text-white py-2.5 px-6 transition-colors duration-200 flex items-center font-medium">
                        <i class="fas fa-search mr-2"></i>
                        <span>Search</span>
                    </button>
                </form>
            </div>

            <!-- Action Icons -->
            <div class="flex items-center space-x-2 md:space-x-3">
                <!-- Mobile Search Toggle -->
                <button
                    @click="searchOpen = !searchOpen"
                    class="lg:hidden p-2 text-gray-700 hover:text-primary hover:bg-gray-100 rounded-full transition-all duration-200"
                    title="Search"
                >
                    <i class="fas fa-search text-lg"></i>
                </button>

                <!-- Wishlist -->
                <a href="{{ route('wishlist.view') }}" class="relative p-2 text-gray-700 hover:text-primary hover:bg-gray-100 rounded-full transition-all duration-200 group" title="Wishlist">
                    <i class="fas fa-heart text-lg"></i>
                    <span id="wishlist-count" class="absolute -top-1 -right-1 bg-danger text-white text-xs w-5 h-5 flex items-center justify-center rounded-full font-bold shadow-md">
                        {{ $wishlistCount ?? 0 }}
                    </span>
                </a>

                <!-- Cart -->
                <div x-data="{ openCart: false }" class="relative">
                    <button
                        @click="openCart = !openCart"
                        class="relative p-2 text-gray-700 hover:text-primary hover:bg-gray-100 rounded-full transition-all duration-200"
                        title="Shopping Cart"
                    >
                        <i class="fas fa-shopping-cart text-lg"></i>
                        <span id="cart-count" class="absolute -top-1 -right-1 bg-primary text-white text-xs w-5 h-5 flex items-center justify-center rounded-full font-bold shadow-md">
                            {{ $cartCount ?? 0 }}
                        </span>
                    </button>

                    <!-- Cart Dropdown -->
                    <div
                        x-show="openCart"
                        @click.away="openCart = false"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95 translate-y-2"
                        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="absolute right-0 mt-3 w-96 bg-white shadow-2xl rounded-xl border border-gray-100 z-50"
                        id="cart-dropdown"
                        style="display: none;"
                    >
                        <div class="p-4 border-b bg-gradient-to-r from-primary/5 to-secondary/5 rounded-t-xl">
                            <h3 class="font-bold text-gray-800 text-lg">Shopping Cart</h3>
                            <p class="text-sm text-gray-500">Review your items</p>
                        </div>
                        @include('frontend_view.components.cards.cartDropdown')
                    </div>
                </div>

                <!-- Account -->
                <div class="relative" x-data="{ open: false }">
                    @auth
                        <button
                            @click="open = !open"
                            class="relative p-2 text-gray-700 hover:text-primary hover:bg-gray-100 rounded-full transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary"
                            title="Account"
                        >
                            <img
                                class="h-7 w-7 md:h-8 md:w-8 rounded-full object-cover ring-2 ring-transparent hover:ring-primary transition"
                                src="{{ Auth::user()->profile_photo ? asset(Auth::user()->profile_photo) : asset('images/default-avatar.png') }}"
                                alt="User"
                            >
                        </button>

                        <div
                            x-show="open"
                            @click.outside="open = false"
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 translate-y-1"
                            x-transition:enter-end="opacity-100 translate-y-0"
                            class="absolute right-0 mt-2 w-56 bg-white border border-gray-100 rounded-xl shadow-xl z-50 overflow-hidden"
                            style="display: none;"
                        >
                            <div class="p-3 bg-gradient-to-r from-primary/5 to-secondary/5 border-b">
                                <p class="text-sm font-semibold text-gray-800">{{ Auth::user()->name }}</p>
                                <p class="text-xs text-gray-500">{{ Auth::user()->email }}</p>
                            </div>
                            <div class="py-2">
                                <a href="{{ route('customer.profile_setting') }}" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-primary hover:text-white transition-colors duration-150">
                                    <i class="fas fa-user-circle mr-3 w-4"></i>My Profile
                                </a>
                                <a href="{{ route('customer.orders') }}" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-primary hover:text-white transition-colors duration-150">
                                    <i class="fas fa-shopping-bag mr-3 w-4"></i>My Orders
                                </a>
                                <a href="{{ route('wishlist.view') }}" class="flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-primary hover:text-white transition-colors duration-150">
                                    <i class="fas fa-heart mr-3 w-4"></i>My Wishlist
                                </a>
                                <hr class="my-2 border-gray-100">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full flex items-center px-4 py-2.5 text-sm text-danger hover:bg-danger hover:text-white transition-colors duration-150">
                                        <i class="fas fa-sign-out-alt mr-3 w-4"></i>Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <button data-modal="login" class="p-2 text-gray-700 hover:text-primary hover:bg-gray-100 rounded-full transition-all duration-200" title="Login">
                            <i class="fas fa-user-circle text-lg"></i>
                        </button>
                    @endauth
                </div>

                <!-- Mobile Menu Toggle -->
                <button
                    @click="mobileMenuOpen = !mobileMenuOpen"
                    class="lg:hidden p-2 text-gray-700 hover:text-primary hover:bg-gray-100 rounded-full transition-all duration-200"
                    title="Menu"
                >
                    <i :class="mobileMenuOpen ? 'fa-times' : 'fa-bars'" class="fas text-2xl"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Search Bar (Collapsible) -->
        <div
            x-show="searchOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-2"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="lg:hidden mt-3 pb-3"
            style="display: none;"
        >
            <form action="{{ route('search') ?? '#' }}" method="GET" class="flex gap-2 rounded-lg overflow-hidden shadow-md">
                <input
                    type="text"
                    name="q"
                    placeholder="Search products..."
                    class="flex-1 py-2.5 px-4 border-0 focus:outline-none focus:ring-2 focus:ring-primary transition"
                    required
                />
                <button type="submit" class="bg-primary hover:bg-primary-dark text-white py-2.5 px-4 transition-colors duration-200">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>
    </div>

    <!-- Category Navigation & Links (Desktop) -->
    <div class="hidden lg:block border-t border-gray-100 bg-gray-50/50 backdrop-blur-sm">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between py-2.5">
                <!-- Categories Dropdown with Modern Design -->
                <div class="relative group">
                    <button class="bg-primary text-white px-5 py-2 rounded-lg flex items-center hover:bg-primary-dark transition-colors duration-200 font-medium shadow-md hover:shadow-lg">
                        <i class="fas fa-th-large mr-2.5"></i>
                        <span>Categories</span>
                        <i class="fas fa-chevron-down ml-2 text-xs opacity-75"></i>
                    </button>

                    <!-- Improved Categories Dropdown - Single Column Compact -->
                    <div class="absolute left-0 top-full mt-1 w-64 bg-white shadow-2xl rounded-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 border border-gray-100 max-h-[600px] overflow-y-auto">
                        <div class="py-2">
                            @foreach($categories as $category)
                            <div class="relative group/cat">
                                <a href="{{route('category',$category->slug)}}" class="px-4 py-2.5 hover:bg-primary hover:text-white flex items-center justify-between text-gray-700 transition-colors duration-150 text-sm">
                                    <div class="flex items-center flex-1 min-w-0">
                                        <i class="fas fa-tag mr-2.5 text-xs opacity-60 flex-shrink-0"></i>
                                        <span class="font-medium truncate">{{ $category->name }}</span>
                                    </div>
                                    @if($category->subcategories->isNotEmpty())
                                    <i class="fas fa-chevron-right text-xs opacity-40 ml-2 flex-shrink-0"></i>
                                    @endif
                                </a>

                                @if($category->subcategories->isNotEmpty())
                                <!-- Vertical Subcategories Popup -->
                                <div class="absolute top-0 left-full ml-1 w-60 bg-white shadow-xl rounded-lg opacity-0 invisible group-hover/cat:opacity-100 group-hover/cat:visible transition-all duration-200 border border-gray-100 z-50">
                                    <div class="p-3 border-b border-gray-100 bg-gray-50">
                                        <p class="text-xs font-semibold text-gray-700">{{ $category->name }}</p>
                                    </div>
                                    <div class="py-2 max-h-96 overflow-y-auto">
                                        @foreach($category->subcategories as $subcategory)
                                        <a href="{{route('category',$subcategory->slug)}}" class="block px-4 py-2.5 hover:bg-primary hover:text-white text-gray-700 transition-colors duration-150 text-sm">
                                            <i class="fas fa-arrow-right mr-2 text-xs opacity-50"></i>{{ $subcategory->name }}
                                        </a>
                                        @endforeach
                                    </div>
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Navigation Links -->
                <nav class="flex items-center space-x-1">
                    <a href="{{url('/')}}" class="px-4 py-2.5 text-gray-700 hover:text-primary font-medium transition-colors duration-150 rounded-lg hover:bg-primary/5">
                        <i class="fas fa-home mr-1.5"></i>Home
                    </a>
                    <a href="{{ url('/#todays-deals') }}" class="px-4 py-2.5 text-gray-700 hover:text-primary font-medium transition-colors duration-150 rounded-lg hover:bg-primary/5">
                        <i class="fas fa-fire mr-1.5 text-orange-500"></i>Today's Deals
                    </a>
                    <a href="{{ url('/#flash-sale') }}" class="px-4 py-2.5 text-gray-700 hover:text-primary font-medium transition-colors duration-150 rounded-lg hover:bg-primary/5">
                        <i class="fas fa-bolt mr-1.5 text-yellow-500"></i>Flash Sale
                    </a>
                    @auth
                        <a href="{{ route('customer.orders') }}" class="px-4 py-2.5 text-gray-700 hover:text-primary font-medium transition-colors duration-150 rounded-lg hover:bg-primary/5">
                            <i class="fas fa-shipping-fast mr-1.5"></i>Track Order
                        </a>
                    @else
                        <a href="#" data-modal="login" class="px-4 py-2.5 text-gray-700 hover:text-primary font-medium transition-colors duration-150 rounded-lg hover:bg-primary/5">
                            <i class="fas fa-shipping-fast mr-1.5"></i>Track Order
                        </a>
                    @endauth
                </nav>
            </div>
        </div>
    </div>

    <!-- Mobile Menu -->
    <div
        x-show="mobileMenuOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 transform -translate-y-2"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform -translate-y-2"
        class="lg:hidden border-t border-gray-100 bg-white shadow-lg"
        style="display: none;"
    >
        <nav class="container mx-auto px-3 sm:px-4 py-4 max-h-[70vh] overflow-y-auto">
            <div class="space-y-1">
                <a href="{{url('/')}}" class="block px-4 py-3 text-gray-700 hover:bg-primary hover:text-white rounded-lg transition-colors duration-150 font-medium">
                    <i class="fas fa-home mr-3 w-5"></i>Home
                </a>
                <a href="{{ url('/#todays-deals') }}" class="block px-4 py-3 text-gray-700 hover:bg-primary hover:text-white rounded-lg transition-colors duration-150 font-medium">
                    <i class="fas fa-fire mr-3 w-5 text-orange-500"></i>Today's Deals
                </a>
                <a href="{{ url('/#flash-sale') }}" class="block px-4 py-3 text-gray-700 hover:bg-primary hover:text-white rounded-lg transition-colors duration-150 font-medium">
                    <i class="fas fa-bolt mr-3 w-5 text-yellow-500"></i>Flash Sale
                </a>
                @auth
                    <a href="{{ route('customer.orders') }}" class="block px-4 py-3 text-gray-700 hover:bg-primary hover:text-white rounded-lg transition-colors duration-150 font-medium">
                        <i class="fas fa-shipping-fast mr-3 w-5"></i>Track Order
                    </a>
                @else
                    <a href="#" data-modal="login" class="block px-4 py-3 text-gray-700 hover:bg-primary hover:text-white rounded-lg transition-colors duration-150 font-medium">
                        <i class="fas fa-shipping-fast mr-3 w-5"></i>Track Order
                    </a>
                @endauth

                <div class="my-4 h-px bg-gray-100"></div>

                <!-- Categories Section - Compact Design -->
                <div x-data="{ categoriesOpen: false }">
                    <button
                        @click="categoriesOpen = !categoriesOpen"
                        class="w-full flex items-center justify-between px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors duration-150 font-medium"
                    >
                        <span><i class="fas fa-th-large mr-3 w-5"></i>Categories</span>
                        <i class="fas fa-chevron-down transition-transform duration-200" :class="{ 'rotate-180': categoriesOpen }"></i>
                    </button>

                    <div x-show="categoriesOpen" x-transition class="mt-2 space-y-1 pl-2 max-h-64 overflow-y-auto" style="display: none;">
                        @foreach($categories as $category)
                        <div x-data="{ subOpen: false }">
                            <div class="flex items-center justify-between">
                                <a href="{{route('category',$category->slug)}}" class="flex-1 block px-4 py-2 text-sm text-gray-600 hover:text-primary hover:bg-gray-50 rounded-lg transition-colors duration-150 font-medium">
                                    <i class="fas fa-tag mr-2 text-xs opacity-50"></i>{{ $category->name }}
                                </a>
                                @if($category->subcategories->isNotEmpty())
                                <button
                                    @click="subOpen = !subOpen"
                                    class="px-2 py-2 text-gray-400 hover:text-primary hover:bg-gray-50 rounded-lg transition-colors duration-150"
                                >
                                    <i class="fas fa-chevron-down text-xs transition-transform duration-200" :class="{ 'rotate-180': subOpen }"></i>
                                </button>
                                @endif
                            </div>

                            @if($category->subcategories->isNotEmpty())
                            <div x-show="subOpen" x-transition class="pl-2 space-y-0.5 mt-1 bg-gray-50 rounded-lg p-2 ml-2" style="display: none;">
                                @foreach($category->subcategories as $subcategory)
                                <a href="{{route('category',$subcategory->slug)}}" class="block px-3 py-1.5 text-xs text-gray-500 hover:text-primary hover:bg-white rounded transition-colors duration-150 font-medium">
                                    {{ $subcategory->name }}
                                </a>
                                @endforeach
                            </div>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </nav>
    </div>
</header>

<!-- Modals -->
<div id="loginModal" class="fixed inset-0 bg-black bg-opacity-50 z-[100] hidden justify-center items-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-md p-6 sm:p-8 relative max-h-[90vh] overflow-y-auto">
        <button id="closeLoginModal" class="absolute top-4 right-4 text-gray-400 hover:text-danger transition text-2xl">
            <i class="fas fa-times"></i>
        </button>
        @include('frontend_view.pages.auth.login')
    </div>
</div>

<div id="registerModal" class="fixed inset-0 bg-black bg-opacity-50 z-[100] hidden justify-center items-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-md p-6 sm:p-8 relative max-h-[90vh] overflow-y-auto">
        <button id="closeRegisterModal" class="absolute top-4 right-4 text-gray-400 hover:text-danger transition text-2xl">
            <i class="fas fa-times"></i>
        </button>
        @include('frontend_view.pages.auth.register')
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function () {
        $('[data-modal="login"]').on('click', function (e) {
            e.preventDefault();
            $('#loginModal').removeClass('hidden').addClass('flex');
        });

        $('[data-modal="register"]').on('click', function (e) {
            e.preventDefault();
            $('#registerModal').removeClass('hidden').addClass('flex');
        });

        $('#closeLoginModal').on('click', function () {
            $('#loginModal').addClass('hidden').removeClass('flex');
        });

        $('#closeRegisterModal').on('click', function () {
            $('#registerModal').addClass('hidden').removeClass('flex');
        });

        // Close modals on outside click
        $('#loginModal, #registerModal').on('click', function(e) {
            if (e.target === this) {
                $(this).addClass('hidden').removeClass('flex');
            }
        });
    });
</script>
@endpush
