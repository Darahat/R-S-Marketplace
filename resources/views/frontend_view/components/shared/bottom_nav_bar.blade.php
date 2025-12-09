<!-- Bottom Navigation Bar (Mobile Only) -->
<nav class="fixed bottom-0 left-0 right-0 z-50 bg-white border-t border-gray-200 shadow-md md:hidden">
    <div class="flex justify-around">
        <a href="{{ route('home') }}" class="flex flex-col items-center justify-center px-4 py-2 text-gray-500 hover:text-indigo-600">
            <i class="fas fa-home text-lg"></i>
            <span class="text-xs">Home</span>
        </a>
        <a href=" " class="flex flex-col items-center justify-center px-4 py-2 text-gray-500 hover:text-indigo-600">
            <i class="fas fa-search text-lg"></i>
            <span class="text-xs">Search</span>
        </a>
        <a href=" " class="flex flex-col items-center justify-center px-4 py-2 text-gray-500 hover:text-indigo-600">
            <i class="fas fa-shopping-cart text-lg"></i>
            <span class="text-xs">Cart</span>
        </a>
        <a href=" " class="flex flex-col items-center justify-center px-4 py-2 text-gray-500 hover:text-indigo-600">
            <i class="fas fa-user text-lg"></i>
            <span class="text-xs">Profile</span>
        </a>
    </div>
</nav>
