<!-- Account Buttons Component -->
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
        <button type="button" onclick="openLoginModal()" class="flex flex-col items-center p-2 text-gray-600 hover:text-primary transition-colors" title="Login / Sign Up">
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
