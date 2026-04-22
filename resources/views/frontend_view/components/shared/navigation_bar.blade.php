<!-- Daraz-style Responsive Header -->
<header class="bg-white shadow-md sticky top-0 z-[1000]" x-data="{ mobileMenuOpen: false, searchOpen: false, openCart: false, openAccount: false }">

    <!-- Top Utility Bar -->
    <div class="bg-primary text-white hidden md:block">
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between py-1.5 text-xs">
                <!-- Left -->
                <div class="flex items-center divide-x divide-white/30">
                    <button type="button" class="pr-4 flex items-center hover:text-yellow-200 transition-colors" disabled title="App coming soon">
                        <i class="fas fa-mobile-alt mr-1.5"></i>Download App
                    </button>
                    <a href="{{ route('support') }}" class="px-4 flex items-center hover:text-yellow-200 transition-colors">
                        <i class="fas fa-headset mr-1.5"></i>Help & Support
                    </a>
                </div>
                <!-- Right -->
                <div class="flex items-center divide-x divide-white/30">
                    <a href="#" class="pr-4 hover:text-yellow-200 transition-colors">Sell With Us</a>
                    @guest
                        <button type="button" @click="openLoginModal()" class="px-4 hover:text-yellow-200 transition-colors font-medium">Login</button>
                        <button type="button" @click="openRegisterModal()" class="pl-4 hover:text-yellow-200 transition-colors font-medium">Sign Up</button>
                    @else
                        <span class="pl-4">Hi, {{ Auth::user()->name }}</span>
                    @endguest
                </div>
            </div>
        </div>
    </div>

    <!-- Main Navigation Row -->
    <div class="container mx-auto px-4 py-3">
        <div class="flex items-center gap-4 lg:gap-6">

            <!-- Logo -->
            <a href="{{ route('home') }}" class="flex-shrink-0">
                <img src="{{ asset('/images/logo/logo.png') }}" alt="MarketGhor" class="h-12 sm:h-14 w-auto">
            </a>

            <!-- Search Bar — takes all remaining space -->
            <form action="{{ route('search') }}" method="GET" class="hidden md:flex flex-1 rounded-md overflow-hidden border-2 border-primary">
                <input
                    type="text"
                    name="q"
                    placeholder="Search products, brands and more..."
                    class="flex-1 py-2.5 px-4 text-sm focus:outline-none"
                />
                <button type="submit" class="bg-primary hover:bg-primary-dark text-white px-5 py-2.5 font-semibold text-sm flex items-center gap-1.5 transition-colors">
                    <i class="fas fa-search"></i>
                    <span class="hidden lg:inline">Search</span>
                </button>
            </form>

            <!-- Action Icons -->
            <div class="flex items-center gap-1 sm:gap-2 flex-shrink-0 ml-auto">

                <!-- Mobile Search Toggle -->
                <button @click="searchOpen = !searchOpen" class="md:hidden p-2 text-gray-600 hover:text-primary rounded-full hover:bg-gray-100 transition-colors" title="Search">
                    <i class="fas fa-search text-xl"></i>
                </button>

                <!-- Wishlist -->
                <a href="{{ route('wishlist.view') }}" class="relative p-2 text-gray-600 hover:text-primary rounded-full hover:bg-gray-100 transition-colors" title="Wishlist">
                    <i class="fas fa-heart text-xl"></i>
                    <span class="absolute -top-0.5 -right-0.5 bg-red-500 text-white text-[10px] min-w-[18px] h-[18px] flex items-center justify-center rounded-full font-bold leading-none px-1">
                        {{ $wishlistCount ?? 0 }}
                    </span>
                </a>

                <!-- Cart Button -->
                <div class="relative">
                    <button id="nav-cart-button" class="relative p-2 text-gray-600 hover:text-primary rounded-full hover:bg-gray-100 transition-colors" title="Cart">
                        <i class="fas fa-shopping-cart text-xl"></i>
                        <span class="absolute -top-0.5 -right-0.5 bg-primary text-white text-[10px] min-w-[18px] h-[18px] flex items-center justify-center rounded-full font-bold leading-none px-1">
                            {{ $cartCount ?? 0 }}
                        </span>
                    </button>
                </div>

                <!-- Account Button -->
                <div class="relative">
                    @auth
                        <button id="nav-avatar-button" class="relative p-1.5 rounded-full hover:bg-gray-100 transition-colors" title="Account">
                            <img
                                class="h-8 w-8 rounded-full object-cover ring-2 ring-primary/30"
                                src="{{ Auth::user()->profile_photo ? asset(Auth::user()->profile_photo) : asset('images/default-avatar.png') }}"
                                alt="User"
                            >
                        </button>
                    @else
                        <button type="button" @click="openLoginModal()" class="flex flex-col items-center p-2 text-gray-600 hover:text-primary transition-colors" title="Login / Sign Up">
                            <i class="fas fa-user-circle text-xl"></i>
                            <span class="text-[10px] mt-0.5 font-medium hidden sm:block">Account</span>
                        </button>
                    @endauth
                </div>

                <!-- Mobile Hamburger -->
                <button
                    @click="mobileMenuOpen = !mobileMenuOpen"
                    class="md:hidden p-2 text-gray-600 hover:text-primary rounded-full hover:bg-gray-100 transition-colors"
                    title="Menu"
                >
                    <i :class="mobileMenuOpen ? 'fas fa-times' : 'fas fa-bars'" class="text-xl"></i>
                </button>
            </div>
        </div>

        <!-- Mobile Search (collapsible) -->
        <div
            x-show="searchOpen"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="md:hidden mt-2"
            style="display: none;"
        >
            <form action="{{ route('search') }}" method="GET" class="flex rounded-md overflow-hidden border-2 border-primary">
                <input type="text" name="q" placeholder="Search products..." class="flex-1 py-2 px-3 text-sm focus:outline-none" />
                <button type="submit" class="bg-primary text-white px-4 py-2 transition-colors">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>
    </div>

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

    <!-- Mobile Menu -->
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

</header>

<!-- Cart Dropdown — outside header so it escapes the sticky stacking context -->
<div
    id="nav-cart-dropdown"
    class="w-96 bg-white shadow-2xl rounded-xl border border-gray-100 overflow-hidden"
    style="display:none; position:fixed; z-index:9999;"
>
    <div class="p-4 border-b">
        <h3 class="font-bold text-gray-800">Shopping Cart</h3>
    </div>
    @include('frontend_view.components.cards.cartDropdown')
</div>

<!-- Account Dropdown — outside header so it escapes the sticky stacking context -->
@auth
<div
    id="nav-account-dropdown"
    class="w-52 bg-white border border-gray-100 rounded-xl shadow-xl overflow-hidden"
    style="display:none; position:fixed; z-index:9999;"
>
    <div class="p-3 border-b bg-primary/5">
        <p class="text-sm font-semibold text-gray-800 truncate">{{ Auth::user()->name }}</p>
        <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
    </div>
    <div class="py-1.5">
        <a href="{{ route('customer.profile') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-primary hover:text-white transition-colors">
            <i class="fas fa-user mr-3 w-4 text-center"></i>My Profile
        </a>
        <a href="{{ route('customer.orders') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-primary hover:text-white transition-colors">
            <i class="fas fa-shopping-bag mr-3 w-4 text-center"></i>My Orders
        </a>
        <a href="{{ route('wishlist.view') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-primary hover:text-white transition-colors">
            <i class="fas fa-heart mr-3 w-4 text-center"></i>Wishlist
        </a>
        <div class="my-1 h-px bg-gray-100 mx-3"></div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full flex items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                <i class="fas fa-sign-out-alt mr-3 w-4 text-center"></i>Logout
            </button>
        </form>
    </div>
</div>
@endauth

<!-- Modals -->
<div id="loginModal" class="fixed inset-0 bg-black bg-opacity-50 z-[2000] hidden justify-center items-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-md p-6 sm:p-8 relative max-h-[90vh] overflow-y-auto">
        <button class="close-modal absolute top-4 right-4 text-gray-400 hover:text-danger transition text-2xl">
            <i class="fas fa-times"></i>
        </button>
        @include('frontend_view.pages.auth.login')
    </div>
</div>

<div id="registerModal" class="fixed inset-0 bg-black bg-opacity-50 z-[2000] hidden justify-center items-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-md p-6 sm:p-8 relative max-h-[90vh] overflow-y-auto">
        <button class="close-modal absolute top-4 right-4 text-gray-400 hover:text-danger transition text-2xl">
            <i class="fas fa-times"></i>
        </button>
        @include('frontend_view.pages.auth.register')
    </div>
</div>

@push('scripts')
<script>
    console.log(typeof Alpine); // Should output "function" if loaded
console.log(document.querySelector('[x-data]')); // Should find your header element
    document.addEventListener('alpine:init', () => {
        // Alpine.js is ready
        console.log('Alpine initialized');
    });

    // Modal functions (global for Alpine)
    window.openLoginModal = function() {
        const modal = document.getElementById('loginModal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    window.openRegisterModal = function() {
        const modal = document.getElementById('registerModal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
    }

    window.closeModals = function() {
        document.getElementById('loginModal')?.classList.add('hidden');
        document.getElementById('loginModal')?.classList.remove('flex');
        document.getElementById('registerModal')?.classList.add('hidden');
        document.getElementById('registerModal')?.classList.remove('flex');
    }

    $(document).ready(function () {
        $(document).on('click', '#nav-cart-button', function () {
            console.log('Cart button clicked');
            const dropdown = $('#nav-cart-dropdown');
            if (dropdown.is(':visible')) {
                dropdown.hide();
                return;
            }
            const rect = this.getBoundingClientRect();
            dropdown.css({
                position: 'fixed',
                top: (rect.bottom + 8) + 'px',
                right: (window.innerWidth - rect.right) + 'px',
                left: 'auto',
                'z-index': 9999
            }).show();
            $('#nav-account-dropdown').hide();
        });

        $(document).on('click', '#nav-avatar-button', function () {
            console.log('Avatar button clicked');
            const dropdown = $('#nav-account-dropdown');
            if (dropdown.is(':visible')) {
                dropdown.hide();
                return;
            }
            const rect = this.getBoundingClientRect();
            dropdown.css({
                position: 'fixed',
                top: (rect.bottom + 8) + 'px',
                right: (window.innerWidth - rect.right) + 'px',
                left: 'auto',
                'z-index': 9999
            }).show();
            $('#nav-cart-dropdown').hide();
        });

        $(document).on('click', function (e) {
            const insideCart = $(e.target).closest('#nav-cart-button, #nav-cart-dropdown').length > 0;
            const insideAccount = $(e.target).closest('#nav-avatar-button, #nav-account-dropdown').length > 0;
            if (!insideCart) $('#nav-cart-dropdown').hide();
            if (!insideAccount) $('#nav-account-dropdown').hide();
        });

        // Close modals
        $('.close-modal').on('click', function() {
            closeModals();
        });

        // Close modals on outside click
        $('#loginModal, #registerModal').on('click', function(e) {
            if (e.target === this) {
                closeModals();
            }
        });

        // Auto-open login modal when redirected with ?auth=login
        const url = new URL(window.location.href);
        if (url.searchParams.get('auth') === 'login') {
            openLoginModal();
            url.searchParams.delete('auth');
            window.history.replaceState({}, '', url.pathname + (url.searchParams.toString() ? '?' + url.searchParams.toString() : '') + url.hash);
        }

        const flashStatus = @json(session('status'));
        if (flashStatus && typeof toastr !== 'undefined') {
            toastr.success(flashStatus);
        }
    });
</script>
@endpush
