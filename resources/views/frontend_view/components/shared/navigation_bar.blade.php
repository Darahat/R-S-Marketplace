<!-- Daraz-style Responsive Header -->
<header class="bg-white shadow-md sticky top-0 z-40"
    x-data="navbar()">

    <!-- Top Utility Bar -->
    @include('frontend_view.components.shared.utility-bar')

    <!-- Main Navigation Row -->
    <div class="container mx-auto px-4 py-3">
        <div class="flex items-center gap-4 lg:gap-6">

            <!-- Logo -->
            <a href="{{ route('home') }}" class="flex-shrink-0">
                <img src="{{ asset('/images/logo/logo.png') }}" alt="MarketGhor" class="h-12 sm:h-14 w-auto">
            </a>

            <!-- Search Bar — takes all remaining space -->
            @include('frontend_view.components.shared.search-bar')

            <!-- Action Icons -->
            <div class="flex items-center gap-1 sm:gap-2 flex-shrink-0 ml-auto">
                @include('frontend_view.components.shared.account-buttons')
            </div>
        </div>
    </div>

    <!-- Bottom Nav Strip (Desktop only) -->
    @include('frontend_view.components.shared.bottom-nav-strip')

    <!-- Mobile Menu -->
    @include('frontend_view.components.shared.mobile-menu')
<!-- Cart Dropdown — outside header so it escapes the sticky stacking context -->
<div
    id="nav-cart-dropdown"
    x-show="openCart"
    :style="cartPosition"
    class="w-96 bg-white shadow-2xl rounded-xl border border-gray-100 overflow-hidden fixed z-50"
>
    <div class="p-4 border-b">
        <h3 class="font-bold text-gray-800">Shopping Cart</h3>
    </div>
    <div id="nav-cart-dropdown-content">
        @include('frontend_view.components.cards.cartDropdown')
    </div>

</div>
<div id="loginModal"
        x-show="loginModalOpen"
         [x-cloak] { display: none !important; } class="fixed inset-0 z-50 flex justify-center items-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-md p-6 sm:p-8 relative max-h-[90vh] overflow-y-auto">
        <button @click="loginModalOpen = false" class="close-modal absolute top-4 right-4 text-gray-400 hover:text-danger transition text-2xl">
            <i class="fas fa-times"></i>
        </button>
        @include('frontend_view.pages.auth.login')
    </div>
</div>

<div id="registerModal" x-show="registerModalOpen" [x-cloak] { display: none !important; }
 class="fixed inset-0 z-50 flex justify-center items-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-md p-6 sm:p-8 relative max-h-[90vh] overflow-y-auto">
        <button @click="registerModalOpen = false" class="close-modal absolute top-4 right-4 text-gray-400 hover:text-danger transition text-2xl">
            <i class="fas fa-times"></i>
        </button>
        @include('frontend_view.pages.auth.register')
    </div>
</div>
@auth
<div
    id="nav-account-dropdown"
    x-show="openAccount"
    :style="accountPosition"
    class="w-52 bg-white border border-gray-100 rounded-xl shadow-xl overflow-hidden fixed z-50"
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
</header>



<!-- Account Dropdown — outside header so it escapes the sticky stacking context -->


<!-- Modals -->
<!-- Page overlay — shown behind modals, independent of Tailwind class scanning -->
<div id="page-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.55); z-index:48;"></div>



@push('scripts')
<script>
document.addEventListener('alpine:init', () => {

    Alpine.data('navbar', () => ({
        openCart: false,
        openAccount: false,
        loginModalOpen: false,    // New state
        registerModalOpen: false,// New state
        cartPosition: {},
        searchOpen: false,
        accountPosition: {},
        mobileMenuOpen :false,

        init() {
            // close on outside click
            document.addEventListener('click', (e) => {
                const cartBtn = document.getElementById('nav-cart-button');
                const cartDrop = document.getElementById('nav-cart-dropdown');

                const accBtn = document.getElementById('nav-avatar-button');
                const accDrop = document.getElementById('nav-account-dropdown');

                if (!cartBtn?.contains(e.target) && !cartDrop?.contains(e.target)) {
                    this.openCart = false;
                }

                if (!accBtn?.contains(e.target) && !accDrop?.contains(e.target)) {
                    this.openAccount = false;
                }
            });

            // ESC close
            document.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    this.openCart = false;
                    this.openAccount = false;
                }
            });

            // resize reposition
            window.addEventListener('resize', () => {
                if (this.openCart) this.setCartPosition();
                if (this.openAccount) this.setAccountPosition();
            });
        },

        toggleCart() {
            this.openCart = !this.openCart;

            if (this.openCart) {
                this.openAccount = false;
                this.refreshCart();
                 this.setCartPosition();
            }
        },

        toggleAccount() {
            this.openAccount = !this.openAccount;

            if (this.openAccount) {
                this.openCart = false;
                this.setAccountPosition();
            }
        },

        setCartPosition() {
            const btn = document.getElementById('nav-cart-button');
            if (!btn) return;

            const rect = btn.getBoundingClientRect();

            this.cartPosition = {
                top: (rect.bottom + 8) + 'px',
                right: (window.innerWidth - rect.right) + 'px',
                left: 'auto'
            };
        },

        setAccountPosition() {
            const btn = document.getElementById('nav-avatar-button');
            if (!btn) return;

            const rect = btn.getBoundingClientRect();

            this.accountPosition = {
                top: (rect.bottom + 8) + 'px',
                right: (window.innerWidth - rect.right) + 'px',
                left: 'auto'
            };
        },

          refreshCart() {
            try {
                const res =  fetch("{{ route('cart.refresh') }}");
                const html =  res.text();
                document.getElementById('nav-cart-dropdown-content').innerHTML = html;
            } catch (e) {
                console.error('Cart refresh failed');
            }
        }
    }));
});
</script>
@endpush
