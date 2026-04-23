<!-- Side Panel -->
<aside class="hidden md:flex md:shrink-0 w-64 flex-col bg-white border-r border-gray-200 shadow-sm">

    {{-- ── Brand Header ── --}}
    <div class="flex items-center gap-2.5 px-5 h-14 border-b border-gray-200 shrink-0">
        <div class="flex items-center justify-center w-7 h-7 rounded-md bg-primary shrink-0">
            <i class="fas fa-store text-white text-xs"></i>
        </div>
        <a href="{{ route('home') }}" class="text-sm font-bold text-gray-900 tracking-tight leading-none">
            R&S <span class="text-primary">Market</span>
        </a>
    </div>

    {{-- ── User Identity Strip ── --}}
    <div class="px-4 py-3 border-b border-gray-100 bg-gray-50/70 shrink-0">
        <div class="flex items-center gap-3">
            @php
                $photo = Auth::user()->profile_photo;
                $initials = collect(explode(' ', Auth::user()->name))->map(fn($w) => strtoupper($w[0]))->take(2)->implode('');
            @endphp

            {{-- Avatar --}}
            @if($photo)
                <img class="h-9 w-9 rounded-full object-cover ring-2 ring-white shadow-sm shrink-0 profile-photo-preview"
                     src="{{ asset($photo) }}" alt="{{ Auth::user()->name }}">
            @else
                <div class="h-9 w-9 rounded-full bg-primary flex items-center justify-center shrink-0 shadow-sm profile-photo-preview">
                    <span class="text-white text-xs font-bold leading-none">{{ $initials }}</span>
                </div>
            @endif

            {{-- Info --}}
            <div class="min-w-0 flex-1">
                <p class="text-xs font-semibold text-gray-800 truncate profile-name-preview">{{ Auth::user()->name }}</p>
                <span class="inline-flex items-center gap-1 mt-0.5">
                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-green-400 shrink-0"></span>
                    <span class="text-[10px] text-gray-400 truncate profile-email-preview">{{ Auth::user()->email }}</span>
                </span>
            </div>

            {{-- Edit --}}
            <a href="{{ route('customer.profile') }}"
               class="shrink-0 w-6 h-6 flex items-center justify-center rounded-md text-gray-400 hover:text-primary hover:bg-gray-200 transition-colors"
               title="Edit profile">
                <i class="fas fa-pen-to-square text-[10px]"></i>
            </a>
        </div>
    </div>

    {{-- ── Navigation ── --}}
    <nav class="flex-1 overflow-y-auto py-3 px-2 space-y-0.5">

        {{-- Overview --}}
        <p class="px-3 pt-1 pb-1.5 text-[10px] font-semibold uppercase tracking-widest text-gray-400">Overview</p>

        <a href="{{ route('customer.dashboard') }}"
           class="group flex items-center gap-3 px-3 py-2 rounded-md text-xs font-medium transition-colors
                  {{ Request::routeIs('customer.dashboard') ? 'bg-primary text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
            <i class="fas fa-tachometer-alt w-4 text-center shrink-0
                      {{ Request::routeIs('customer.dashboard') ? 'text-white' : 'text-gray-400 group-hover:text-gray-600' }}"></i>
            Dashboard
        </a>

        {{-- Shopping --}}
        <p class="px-3 pt-3 pb-1.5 text-[10px] font-semibold uppercase tracking-widest text-gray-400">Shopping</p>

        {{-- Orders accordion --}}
        <details class="group/orders" {{ Request::routeIs('customer.orders*') ? 'open' : '' }}>
            <summary class="list-none cursor-pointer group flex items-center justify-between px-3 py-2 rounded-md text-xs font-medium transition-colors
                           {{ Request::routeIs('customer.orders*') ? 'bg-primary-50 text-primary-800' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                <span class="flex items-center gap-3">
                    <i class="fas fa-shopping-bag w-4 text-center shrink-0
                              {{ Request::routeIs('customer.orders*') ? 'text-primary' : 'text-gray-400' }}"></i>
                    My Orders
                </span>
                <i class="fas fa-chevron-right text-[9px] text-gray-400 transition-transform group-open/orders:rotate-90"></i>
            </summary>
            <div class="mt-0.5 ml-7 pl-2 border-l border-gray-200 space-y-0.5 pb-1">
                <a href="{{ route('customer.orders') }}"
                   class="block px-2 py-1.5 rounded text-xs transition-colors
                          {{ Request::routeIs('customer.orders') && !request('status') ? 'text-primary-700 font-semibold' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-50' }}">
                    All Orders
                </a>
                <a href="{{ route('customer.orders') }}?status=pending"
                   class="block px-2 py-1.5 rounded text-xs transition-colors
                          {{ request('status') === 'pending' ? 'text-primary-700 font-semibold' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-50' }}">
                    Pending
                </a>
                <a href="{{ route('customer.orders') }}?status=completed"
                   class="block px-2 py-1.5 rounded text-xs transition-colors
                          {{ request('status') === 'completed' ? 'text-primary-700 font-semibold' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-50' }}">
                    Completed
                </a>
            </div>
        </details>

        <a href="{{ route('customer.wishlist') }}"
           class="group flex items-center gap-3 px-3 py-2 rounded-md text-xs font-medium transition-colors
                  {{ Request::routeIs('customer.wishlist') ? 'bg-primary text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
            <i class="fas fa-heart w-4 text-center shrink-0
                      {{ Request::routeIs('customer.wishlist') ? 'text-white' : 'text-gray-400 group-hover:text-gray-600' }}"></i>
            Wishlist
            @if($wishlistCount > 0)
            <span class="ml-auto text-[10px] leading-none px-1.5 py-0.5 rounded-full font-semibold
                         {{ Request::routeIs('customer.wishlist') ? 'bg-white/20 text-white' : 'bg-primary-100 text-primary-700' }}">
                {{ $wishlistCount }}
            </span>
            @endif
        </a>

        {{-- Account --}}
        <p class="px-3 pt-3 pb-1.5 text-[10px] font-semibold uppercase tracking-widest text-gray-400">Account</p>

        {{-- Account Settings accordion --}}
        @php $inAccount = Request::routeIs('customer.profile*') || Request::routeIs('customer.profile_setting*'); @endphp
        <details class="group/account" {{ $inAccount ? 'open' : '' }}>
            <summary class="list-none cursor-pointer group flex items-center justify-between px-3 py-2 rounded-md text-xs font-medium transition-colors
                           {{ $inAccount ? 'bg-primary-50 text-primary-800' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                <span class="flex items-center gap-3">
                    <i class="fas fa-user-circle w-4 text-center shrink-0 {{ $inAccount ? 'text-primary' : 'text-gray-400' }}"></i>
                    My Profile
                </span>
                <i class="fas fa-chevron-right text-[9px] text-gray-400 transition-transform group-open/account:rotate-90"></i>
            </summary>
            <div class="mt-0.5 ml-7 pl-2 border-l border-gray-200 space-y-0.5 pb-1">
                <a href="{{ route('customer.profile') }}"
                   class="block px-2 py-1.5 rounded text-xs transition-colors
                          {{ Request::routeIs('customer.profile') ? 'text-primary-700 font-semibold' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-50' }}">
                    Profile Info
                </a>
                <a href="{{ route('customer.profile_setting') }}"
                   class="block px-2 py-1.5 rounded text-xs transition-colors
                          {{ Request::routeIs('customer.profile_setting') ? 'text-primary-700 font-semibold' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-50' }}">
                    Settings
                </a>
            </div>
        </details>

        <a href="{{ route('customer.addresses.index') }}"
           class="group flex items-center gap-3 px-3 py-2 rounded-md text-xs font-medium transition-colors
                  {{ Request::routeIs('customer.addresses.*') ? 'bg-primary text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
            <i class="fas fa-map-marker-alt w-4 text-center shrink-0
                      {{ Request::routeIs('customer.addresses.*') ? 'text-white' : 'text-gray-400 group-hover:text-gray-600' }}"></i>
            Address Book
        </a>

        <a href="{{ route('customer.payment_methods.index') }}"
           class="group flex items-center gap-3 px-3 py-2 rounded-md text-xs font-medium transition-colors
                  {{ Request::routeIs('customer.payment_methods.*') ? 'bg-primary text-white' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
            <i class="fas fa-credit-card w-4 text-center shrink-0
                      {{ Request::routeIs('customer.payment_methods.*') ? 'text-white' : 'text-gray-400 group-hover:text-gray-600' }}"></i>
            Payment Methods
        </a>

    </nav>

    {{-- ── Logout Footer ── --}}
    <div class="shrink-0 px-2 py-3 border-t border-gray-200">
        <form method="POST" action="{{ route('logout') }}" data-no-loading>
            @csrf
            <button type="submit"
                    class="w-full flex items-center gap-3 px-3 py-2 rounded-md text-xs font-medium text-red-500 hover:bg-red-50 hover:text-red-600 transition-colors">
                <i class="fas fa-sign-out-alt w-4 text-center shrink-0"></i>
                Sign Out
            </button>
        </form>
    </div>

</aside>
