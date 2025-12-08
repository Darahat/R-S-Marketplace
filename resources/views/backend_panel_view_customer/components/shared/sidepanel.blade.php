<!-- Side Panel -->
<div class="hidden md:flex md:flex-shrink-0">
  <div class="flex flex-col w-64 bg-gradient-to-b from-primary to-secondary">
      <div class="flex items-center justify-center h-16 px-4 border-b border-white border-opacity-20">
        <a href="{{route('home')}}"><span class="text-white text-xl font-semibold">MarketGhor</span></a>  
      </div>
      <div class="flex flex-col flex-grow px-4 py-4 overflow-y-auto">
          <!-- User Profile -->
          <div class="flex items-center px-4 py-3 mb-4 bg-white bg-opacity-10 rounded-lg">
              <img class="h-10 w-10 rounded-full object-cover profile-photo-preview"  src="{{ Auth::user()->profile_photo ? asset(Auth::user()->profile_photo) : asset('images/default-avatar.png') }}" alt="User Avatar">
              <div class="ml-3">
                  <p class="text-sm font-medium text-white profile-name-preview">{{ Auth::user()->name }}</p>
                  <p class="text-xs text-white text-opacity-70 profile-email-preview">{{ Auth::user()->email }}</p>
              </div>
          </div>

          <!-- Navigation Links -->
          <nav class="space-y-1">
              <a href="{{ route('customer.dashboard') }}" 
                 class="@if(Route::is('customer.dashboard')) bg-white bg-opacity-20 @endif flex items-center px-4 py-3 text-sm font-medium text-white rounded-lg hover:bg-white hover:bg-opacity-10 transition">
                  <i class="fas fa-tachometer-alt mr-3"></i>
                  Dashboard
              </a>
           
              <a href="{{ route('customer.profile_setting') }}" 
              class="@if(Route::is('customer.profile_setting')) bg-white bg-opacity-20 @endif flex items-center px-4 py-3 text-sm font-medium text-white rounded-lg hover:bg-white hover:bg-opacity-10 transition">
               <i class="fas fa-cog mr-3"></i>
               Profile
           </a>
           <a href="{{ route('customer.addresses.index') }}" 
           class="@if(Route::is('customer.addresses.index')) bg-white bg-opacity-20 @endif flex items-center px-4 py-3 text-sm font-medium text-white rounded-lg hover:bg-white hover:bg-opacity-10 transition">
            <i class="fas fa-cog mr-3"></i>
            Address Book
        </a>
        <a href="{{ route('customer.orders') }}" 
        class="@if(Route::is('customer.addresses.index')) bg-white bg-opacity-20 @endif flex items-center px-4 py-3 text-sm font-medium text-white rounded-lg hover:bg-white hover:bg-opacity-10 transition">
         <i class="fas fa-cog mr-3"></i>
         My Orders
     </a>
{{--               
              <a href="{{ route('customer.orders') }}" 
                 class="@if(Route::is('customer.orders')) bg-white bg-opacity-20 @endif flex items-center px-4 py-3 text-sm font-medium text-white rounded-lg hover:bg-white hover:bg-opacity-10 transition">
                  <i class="fas fa-shopping-bag mr-3"></i>
                  My Orders
              </a>
              
              <a href="{{ route('customer.wishlist') }}" 
                 class="@if(Route::is('customer.wishlist')) bg-white bg-opacity-20 @endif flex items-center px-4 py-3 text-sm font-medium text-white rounded-lg hover:bg-white hover:bg-opacity-10 transition">
                  <i class="fas fa-heart mr-3"></i>
                  Wishlist
                  <span class="ml-auto bg-white bg-opacity-20 text-xs rounded-full px-2 py-1">{{ Auth::user()->wishlistItems()->count() }}</span>
              </a>
              
              <a href="{{ route('customer.profile') }}" 
                 class="@if(Route::is('customer.profile')) bg-white bg-opacity-20 @endif flex items-center px-4 py-3 text-sm font-medium text-white rounded-lg hover:bg-white hover:bg-opacity-10 transition">
                  <i class="fas fa-user mr-3"></i>
                  Profile
              </a>
              
             
              
              <form method="POST" action="{{ route('logout') }}">
                  @csrf
                  <button type="submit" class="w-full flex items-center px-4 py-3 text-sm font-medium text-white rounded-lg hover:bg-white hover:bg-opacity-10 transition">
                      <i class="fas fa-sign-out-alt mr-3"></i>
                      Logout
                  </button>
              </form> --}}
          </nav>
      </div>
  </div>
</div>