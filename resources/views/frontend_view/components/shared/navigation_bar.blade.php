<!-- Enhanced Responsive Header with Mobile Menu -->
<header class="bg-white shadow-md sticky top-0 z-50" x-data="{ mobileMenuOpen: false, searchOpen: false }">
    <!-- Top Bar -->
    <div class="bg-gradient-to-r from-primary to-secondary text-white">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between py-2 text-xs sm:text-sm">
                <!-- Left side -->
                <div class="flex items-center space-x-2 sm:space-x-4">
                    <a href="#" class="hover:text-yellow-300 transition">Download App</a>
                </div>

                <!-- Right side -->
                <div class="flex items-center space-x-2 sm:space-x-4">
                    @guest
                        <div class="flex items-center space-x-1">
                            <button data-modal="login" class="hover:text-yellow-300 transition">Login</button>
                            <span>/</span>
                            <button data-modal="register" class="hover:text-yellow-300 transition">Register</button>
                        </div>
                    @endguest
                </div>
            </div>
        </div>
    </div>

    <!-- Main Navigation -->
    <div class="container mx-auto px-4 py-3">
        <div class="flex items-center justify-between gap-4">
            <!-- Logo -->
            <div class="flex-shrink-0">
                <a href="{{route('home')}}" class="flex items-center">
                    <img src="{{asset('/images/logo/logo.png')}}" alt="MarketGhor" class="h-8 sm:h-10">
                </a>
            </div>

            <!-- Desktop Search Bar -->
            <div class="hidden lg:flex flex-1 max-w-2xl">
                <div class="flex w-full">
                    <input
                        type="text"
                        placeholder="Search products..."
                        class="flex-1 py-2.5 px-4 border-2 border-primary rounded-l-lg focus:outline-none focus:border-secondary transition"
                    />
                    <button class="bg-primary hover:bg-secondary text-white py-2.5 px-6 rounded-r-lg transition flex items-center">
                        <i class="fas fa-search"></i>
                        <span class="hidden xl:inline ml-2">Search</span>
                    </button>
                </div>
            </div>

            <!-- Action Icons -->
            <div class="flex items-center space-x-3 sm:space-x-4">
                <!-- Mobile Search Toggle -->
                <button
                    @click="searchOpen = !searchOpen"
                    class="lg:hidden flex flex-col items-center text-gray-700 hover:text-primary transition"
                >
                    <i class="fas fa-search text-xl"></i>
                </button>

                <!-- Wishlist -->
                <a href="{{ route('wishlist.view') }}" class="relative flex flex-col items-center text-gray-700 hover:text-primary transition group">
                    <i class="fas fa-heart text-xl group-hover:scale-110 transition-transform"></i>
                    <span class="text-xs mt-1 hidden sm:block">Wishlist</span>
                    <span id="wishlist-count" class="absolute -top-2 -right-2 bg-danger text-white text-xs w-5 h-5 flex items-center justify-center rounded-full font-semibold">
                        {{ $wishlistCount ?? 0 }}
                    </span>
                </a>

                <!-- Cart -->
                <div x-data="{ openCart: false }" class="relative">
                    <button
                        @click="openCart = !openCart"
                        class="relative flex flex-col items-center text-gray-700 hover:text-primary transition group"
                    >
                        <i class="fas fa-shopping-cart text-xl group-hover:scale-110 transition-transform"></i>
                        <span class="text-xs mt-1 hidden sm:block">Cart</span>
                        <span id="cart-count" class="absolute -top-2 -right-2 bg-primary text-white text-xs rounded-full h-5 w-5 flex items-center justify-center font-semibold">
                            {{ $cartCount ?? 0 }}
                        </span>
                    </button>

                    <!-- Cart Dropdown -->
                    <div
                        x-show="openCart"
                        @click.away="openCart = false"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 scale-95"
                        x-transition:enter-end="opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-150"
                        x-transition:leave-start="opacity-100 scale-100"
                        x-transition:leave-end="opacity-0 scale-95"
                        class="absolute right-0 mt-2 w-80 sm:w-96 bg-white shadow-2xl rounded-lg border border-gray-200 z-50"
                        id="cart-dropdown"
                        style="display: none;"
                    >
                        <div class="p-4 border-b bg-gray-50">
                            <h3 class="font-semibold text-gray-800 text-lg">Shopping Cart</h3>
                        </div>
                        @include('frontend_view.components.cards.cartDropdown')
                    </div>
                </div>

                <!-- Account -->
                <div class="relative" x-data="{ open: false }">
                    @auth
                        <button
                            @click="open = !open"
                            class="flex flex-col items-center text-gray-700 hover:text-primary transition focus:outline-none group"
                        >
                            <img
                                class="h-8 w-8 rounded-full object-cover ring-2 ring-transparent group-hover:ring-primary transition"
                                src="{{ Auth::user()->profile_photo ? asset(Auth::user()->profile_photo) : asset('images/default-avatar.png') }}"
                                alt="User"
                            >
                            <span class="text-xs mt-1 hidden sm:block">Account</span>
                        </button>

                        <div
                            x-show="open"
                            @click.outside="open = false"
                            x-transition
                            class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-lg shadow-xl z-50"
                            style="display: none;"
                        >
                            <div class="py-2">
                                <a href="{{ route('customer.profile_setting') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-primary hover:text-white transition">
                                    <i class="fas fa-user-circle mr-2"></i>My Profile
                                </a>
                                <a href="{{ route('customer.orders') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-primary hover:text-white transition">
                                    <i class="fas fa-shopping-bag mr-2"></i>My Orders
                                </a>
                                <hr class="my-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-danger hover:bg-danger hover:text-white transition">
                                        <i class="fas fa-sign-out-alt mr-2"></i>Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <button data-modal="login" class="flex flex-col items-center text-gray-700 hover:text-primary transition">
                            <i class="fas fa-user-circle text-xl"></i>
                            <span class="text-xs mt-1 hidden sm:block">Login</span>
                        </button>
                    @endauth
                </div>

                <!-- Mobile Menu Toggle -->
                <button
                    @click="mobileMenuOpen = !mobileMenuOpen"
                    class="lg:hidden flex flex-col items-center text-gray-700 hover:text-primary transition"
                >
                    <i :class="mobileMenuOpen ? 'fa-times' : 'fa-bars'" class="fas text-2xl"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Search Bar (Collapsible) -->
        <div
            x-show="searchOpen"
            x-transition
            class="lg:hidden mt-3 pb-2"
            style="display: none;"
        >
            <div class="flex">
                <input
                    type="text"
                    placeholder="Search products..."
                    class="flex-1 py-2 px-4 border-2 border-primary rounded-l-lg focus:outline-none focus:border-secondary"
                />
                <button class="bg-primary hover:bg-secondary text-white py-2 px-4 rounded-r-lg transition">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Category Navigation & Links (Desktop) -->
    <div class="hidden lg:block border-t border-gray-200 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between py-2">
                <!-- Categories Dropdown -->
                <div class="relative group">
                    <button class="bg-primary text-white px-6 py-2.5 rounded-lg flex items-center hover:bg-secondary transition font-medium">
                        <i class="fas fa-th-large mr-2"></i>
                        <span>All Categories</span>
                        <i class="fas fa-chevron-down ml-2 text-xs"></i>
                    </button>

                    <!-- Mega Menu -->
                    <div class="absolute left-0 top-full mt-1 w-72 bg-white shadow-2xl rounded-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50 border border-gray-100 max-h-[500px] overflow-y-auto">
                        <ul class="py-2">
                            @foreach($categories as $category)
                            <li class="relative group/sub border-b border-gray-100 last:border-0">
                                <a href="{{route('category',$category->slug)}}" class="px-4 py-3 hover:bg-primary hover:text-white flex items-center justify-between text-gray-700 transition">
                                    <div class="flex items-center">
                                        <i class="fas fa-tag mr-3 text-sm"></i>
                                        <span class="font-medium">{{ $category->name }}</span>
                                    </div>
                                    @if($category->subcategories->isNotEmpty())
                                    <i class="fas fa-chevron-right text-xs"></i>
                                    @endif
                                </a>

                                @if($category->subcategories->isNotEmpty())
                                <ul class="absolute top-0 left-full ml-1 w-64 bg-white shadow-xl rounded-lg opacity-0 invisible group-hover/sub:opacity-100 group-hover/sub:visible transition-all duration-200 border border-gray-100 z-50 max-h-[400px] overflow-y-auto">
                                    @foreach($category->subcategories as $subcategory)
                                    <li class="border-b border-gray-100 last:border-0">
                                        <a href="{{route('category',$subcategory->slug)}}" class="block px-4 py-2.5 hover:bg-primary hover:text-white text-gray-700 transition">
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

                <!-- Navigation Links -->
                <nav class="flex items-center space-x-6">
                    <a href="{{url('/')}}" class="text-gray-700 hover:text-primary font-medium transition py-2 border-b-2 border-transparent hover:border-primary">
                        <i class="fas fa-home mr-1"></i>Home
                    </a>
                    <a href="{{ url('/#todays-deals') }}" class="text-gray-700 hover:text-primary font-medium transition py-2 border-b-2 border-transparent hover:border-primary">
                        <i class="fas fa-fire mr-1"></i>Today's Deals
                    </a>
                    <a href="{{ url('/#flash-sale') }}" class="text-gray-700 hover:text-primary font-medium transition py-2 border-b-2 border-transparent hover:border-primary">
                        <i class="fas fa-bolt mr-1"></i>Flash Sale
                    </a>
                    @auth
                        <a href="{{ route('customer.orders') }}" class="text-gray-700 hover:text-primary font-medium transition py-2 border-b-2 border-transparent hover:border-primary">
                            <i class="fas fa-shipping-fast mr-1"></i>Track Order
                        </a>
                    @else
                        <a href="#" data-modal="login" class="text-gray-700 hover:text-primary font-medium transition py-2 border-b-2 border-transparent hover:border-primary">
                            <i class="fas fa-shipping-fast mr-1"></i>Track Order
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
        x-transition:enter-start="opacity-0 transform -translate-y-4"
        x-transition:enter-end="opacity-100 transform translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 transform translate-y-0"
        x-transition:leave-end="opacity-0 transform -translate-y-4"
        class="lg:hidden border-t border-gray-200 bg-white shadow-lg"
        style="display: none;"
    >
        <nav class="container mx-auto px-4 py-4 max-h-[70vh] overflow-y-auto">
            <div class="space-y-1">
                <a href="{{url('/')}}" class="block px-4 py-3 text-gray-700 hover:bg-primary hover:text-white rounded-lg transition font-medium">
                    <i class="fas fa-home mr-3 w-5"></i>Home
                </a>
                <a href="{{ url('/#todays-deals') }}" class="block px-4 py-3 text-gray-700 hover:bg-primary hover:text-white rounded-lg transition font-medium">
                    <i class="fas fa-fire mr-3 w-5"></i>Today's Deals
                </a>
                <a href="{{ url('/#flash-sale') }}" class="block px-4 py-3 text-gray-700 hover:bg-primary hover:text-white rounded-lg transition font-medium">
                    <i class="fas fa-bolt mr-3 w-5"></i>Flash Sale
                </a>
                @auth
                    <a href="{{ route('customer.orders') }}" class="block px-4 py-3 text-gray-700 hover:bg-primary hover:text-white rounded-lg transition font-medium">
                        <i class="fas fa-shipping-fast mr-3 w-5"></i>Track Order
                    </a>
                @else
                    <a href="#" data-modal="login" class="block px-4 py-3 text-gray-700 hover:bg-primary hover:text-white rounded-lg transition font-medium">
                        <i class="fas fa-shipping-fast mr-3 w-5"></i>Track Order
                    </a>
                @endauth

                <hr class="my-3">

                <div x-data="{ categoriesOpen: false }">
                    <button
                        @click="categoriesOpen = !categoriesOpen"
                        class="w-full flex items-center justify-between px-4 py-3 text-gray-700 hover:bg-gray-100 rounded-lg transition font-medium"
                    >
                        <span><i class="fas fa-th-large mr-3 w-5"></i>All Categories</span>
                        <i class="fas fa-chevron-down transition-transform" :class="{ 'rotate-180': categoriesOpen }"></i>
                    </button>

                    <div x-show="categoriesOpen" x-transition class="mt-2 space-y-1 pl-4" style="display: none;">
                        @foreach($categories as $category)
                        <div x-data="{ subOpen: false }">
                            <div class="flex items-center justify-between">
                                <a href="{{route('category',$category->slug)}}" class="flex-1 block px-4 py-2 text-sm text-gray-600 hover:text-primary transition">
                                    {{ $category->name }}
                                </a>
                                @if($category->subcategories->isNotEmpty())
                                <button
                                    @click="subOpen = !subOpen"
                                    class="px-2 py-2 text-gray-400 hover:text-primary"
                                >
                                    <i class="fas fa-chevron-down text-xs transition-transform" :class="{ 'rotate-180': subOpen }"></i>
                                </button>
                                @endif
                            </div>

                            @if($category->subcategories->isNotEmpty())
                            <div x-show="subOpen" x-transition class="pl-4 space-y-1 mt-1" style="display: none;">
                                @foreach($category->subcategories as $subcategory)
                                <a href="{{route('category',$subcategory->slug)}}" class="block px-4 py-2 text-xs text-gray-500 hover:text-primary transition">
                                    • {{ $subcategory->name }}
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
