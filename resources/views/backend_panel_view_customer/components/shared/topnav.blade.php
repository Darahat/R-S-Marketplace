<!-- Top Navigation -->
<header class="bg-white shadow-sm">
    <div class="flex items-center justify-between px-6 py-3">
        <!-- Mobile Menu Button -->
        <div class="md:hidden">
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-gray-500 focus:outline-none">
                <i class="fas fa-bars text-xl"></i>
            </button>
        </div>

        <!-- Spacer (search removed: product search belongs on the storefront, not the customer dashboard) -->
        <div class="flex-1"></div>

        <!-- Right Side Icons -->
        <div class="flex items-center space-x-4">
            <!-- Notifications -->
            <div class="relative">
                <button id="notification-bell-button" type="button" class="text-gray-500 hover:text-primary focus:outline-none relative">
                    <i class="fas fa-bell text-xl"></i>
                    <span id="notification-badge" class="absolute -top-2 -right-2 min-w-[18px] h-[18px] px-1 rounded-full bg-red-500 text-white text-[10px] leading-[18px] text-center font-semibold hidden">0</span>
                </button>

                <div id="notification-panel" class="hidden absolute right-0 mt-3 w-80 bg-white rounded-lg shadow-lg border border-gray-100 z-50">
                    <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-gray-800">Notifications</h3>
                        <button id="notification-read-all" type="button" class="text-xs text-primary hover:underline">Mark all as read</button>
                    </div>

                    <div id="notification-list" class="max-h-80 overflow-y-auto">
                        <div class="px-4 py-4 text-sm text-gray-500">Loading...</div>
                    </div>

                    <div class="px-4 py-2 border-t border-gray-100 text-xs text-gray-500">
                        Showing unread items only
                    </div>
                </div>
            </div>

            <!-- Messages -->
            <div class="relative">
                <button class="text-gray-500 hover:text-primary focus:outline-none">
                    <i class="fas fa-envelope text-xl"></i>
                    <span class="absolute top-0 right-0 h-2 w-2 rounded-full bg-red-500"></span>
                </button>
            </div>

            <!-- Cart -->
            {{-- <a href="{{ route('cart.view') }}" class="text-gray-500 hover:text-primary relative">
                <i class="fas fa-shopping-cart text-xl"></i>
                <span class="absolute -top-2 -right-2 bg-primary text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">{{ Cart::count() }}</span>
            </a> --}}

            <!-- User Dropdown -->
            <div class="relative ml-4 topnav-user-menu">
                <button type="button" class="flex items-center focus:outline-none topnav-user-toggle">
                    <img class="h-8 w-8 rounded-full object-cover profile-photo-preview" src="{{ Auth::user()->profile_photo ? asset(Auth::user()->profile_photo) : asset('images/default-avatar.png') }}" alt="User Avatar">
                    <span class="ml-2 text-sm font-medium text-gray-700 hidden md:inline profile-name-preview">{{ Auth::user()->name }}</span>
                    <i class="fas fa-chevron-down ml-1 text-xs text-gray-500 hidden md:inline"></i>
                </button>

                 <div class="topnav-user-dropdown hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                    <a href="{{ route('customer.profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-user mr-2"></i> Profile
                    </a>
                    <a href="{{ route('customer.profile_setting') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-cog mr-2"></i> Settings
                    </a>
                    <div class="border-t border-gray-200"></div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</header>

@push('scripts')
<script>
    $(document).ready(function () {
        if (window.__topnavUserMenuBound) {
            return;
        }
        window.__topnavUserMenuBound = true;

        $(document).on('click', '.topnav-user-toggle', function (e) {
            e.preventDefault();
            e.stopPropagation();

            const menu = $(this).closest('.topnav-user-menu');
            const dropdown = menu.find('.topnav-user-dropdown');

            $('.topnav-user-dropdown').not(dropdown).addClass('hidden');
            dropdown.toggleClass('hidden');
        });

        $(document).on('click', '.topnav-user-dropdown', function (e) {
            e.stopPropagation();
        });

        $(document).on('click', function () {
            $('.topnav-user-dropdown').addClass('hidden');
        });
    });
</script>
@endpush
