<!-- Top Navigation -->
<header class="bg-white shadow-sm">
    <div class="flex items-center justify-between px-6 py-3">
        <!-- Mobile Menu Button -->
        <div class="md:hidden">
            <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-gray-500 focus:outline-none">
                <i class="fas fa-bars text-xl"></i>
            </button>
        </div>

        <!-- Search Bar -->
        <div class="flex-1 max-w-md mx-4">
            <div class="relative">
                <input type="text" placeholder="Search..." 
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent">
                <div class="absolute left-3 top-2.5 text-gray-400">
                    <i class="fas fa-search"></i>
                </div>
            </div>
        </div>

        <!-- Right Side Icons -->
        <div class="flex items-center space-x-4">
            <!-- Notifications -->
            <div class="relative">
                <button class="text-gray-500 hover:text-primary focus:outline-none">
                    <i class="fas fa-bell text-xl"></i>
                    <span class="absolute top-0 right-0 h-2 w-2 rounded-full bg-red-500"></span>
                </button>
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
            <div class="relative ml-4" x-data="{ open: false }">
                <button @click="open = !open" class="flex items-center focus:outline-none">
                    <img class="h-8 w-8 rounded-full object-cover profile-photo-preview" src="{{ Auth::user()->profile_photo ? asset(Auth::user()->profile_photo) : asset('images/default-avatar.png') }}" alt="User Avatar">
                    <span class="ml-2 text-sm font-medium text-gray-700 hidden md:inline profile-name-preview">{{ Auth::user()->name }}</span>
                    <i class="fas fa-chevron-down ml-1 text-xs text-gray-500 hidden md:inline"></i>
                </button>
                
                <div x-show="open" @click.away="open = false" 
                     class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                    {{-- <a href="{{ route('customer.profile') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-user mr-2"></i> Profile
                    </a>
                    <a href="{{ route('customer.profile_setting') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                        <i class="fas fa-cog mr-2"></i> Settings
                    </a> --}}
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