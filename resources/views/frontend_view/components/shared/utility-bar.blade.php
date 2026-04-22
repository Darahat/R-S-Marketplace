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
