<!-- Search Bar Component -->
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
