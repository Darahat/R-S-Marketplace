<!-- Daraz-style Responsive Header -->
<header class="bg-white shadow-md sticky top-0 z-40" x-data="{ mobileMenuOpen: false, searchOpen: false, openCart: false, openAccount: false }">

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

</header>

<!-- Cart Dropdown — outside header so it escapes the sticky stacking context -->
<div
    id="nav-cart-dropdown"
    class="w-96 bg-white shadow-2xl rounded-xl border border-gray-100 overflow-hidden"
    style="display:none; position:fixed; z-index:41;"
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
    style="display:none; position:fixed; z-index:41;"
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
<!-- Page overlay — shown behind modals, independent of Tailwind class scanning -->
<div id="page-overlay" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.55); z-index:48;"></div>

<div id="loginModal" class="fixed inset-0 z-50 hidden justify-center items-center p-4">
    <div class="bg-white rounded-2xl w-full max-w-md p-6 sm:p-8 relative max-h-[90vh] overflow-y-auto">
        <button class="close-modal absolute top-4 right-4 text-gray-400 hover:text-danger transition text-2xl">
            <i class="fas fa-times"></i>
        </button>
        @include('frontend_view.pages.auth.login')
    </div>
</div>

<div id="registerModal" class="fixed inset-0 z-50 hidden justify-center items-center p-4">
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
            document.getElementById('page-overlay').style.display = 'block';
            document.body.classList.add('overflow-hidden');
        }
    }

    window.openRegisterModal = function() {
        const modal = document.getElementById('registerModal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.getElementById('page-overlay').style.display = 'block';
            document.body.classList.add('overflow-hidden');
        }
    }

    window.closeModals = function() {
        document.getElementById('loginModal')?.classList.add('hidden');
        document.getElementById('loginModal')?.classList.remove('flex');
        document.getElementById('registerModal')?.classList.add('hidden');
        document.getElementById('registerModal')?.classList.remove('flex');
        document.getElementById('page-overlay').style.display = 'none';
        document.body.classList.remove('overflow-hidden');
    }

    $(document).ready(function () {
        $(document).on('click', '[data-modal="login"]', function () {
            openLoginModal();
        });

        $(document).on('click', '[data-modal="register"]', function () {
            openRegisterModal();
        });

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
                'z-index': 41
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
                'z-index': 41
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

        // Close modals on overlay click
        $('#page-overlay').on('click', function() {
            closeModals();
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
