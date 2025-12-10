<div class="group relative bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 border border-gray-100 hover:border-primary/30 card-hover">
    <!-- Product Image -->
    <div class="relative aspect-square bg-gray-50 overflow-hidden">
        <a href="{{ route('product',['slug'=> $product->slug ]) }}" class="block h-full">
            <img
                src="{{ $product->image_url ?? $product->image ?? 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=500' }}"
                alt="{{ $product->name }}"
                class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                loading="lazy"
            >
        </a>

        <!-- Badges -->
        <div class="absolute top-2 left-2 flex flex-col gap-1.5 z-10">
            @if(!empty($product->is_new))
            <span class="inline-flex items-center bg-success text-white text-xs font-semibold px-2 py-1 rounded-full shadow-sm">
                <i class="fas fa-sparkles mr-1"></i>New
            </span>
            @endif
            @if(!empty($product->discount_price) && $product->discount_price > 0)
            <span class="inline-flex items-center bg-danger text-white text-xs font-bold px-2 py-1 rounded-full shadow-sm">
                -{{ number_format((($product->price - $product->discount_price) / $product->price) * 100) }}%
            </span>
            @endif
        </div>

        <!-- Wishlist Button -->
        <button
            class="wishlist-toggle absolute top-2 right-2 p-2 bg-white/95 backdrop-blur-sm rounded-full shadow-md hover:bg-white hover:scale-110 transition-all duration-200 z-10"
            data-id="{{ $product->id }}"
            aria-label="Add to wishlist"
        >
            @php $wishlisted = $product->is_wishlisted ?? false; @endphp
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 {{ $wishlisted ? 'text-danger fill-danger' : 'text-gray-400' }}" viewBox="0 0 20 20" fill="{{ $wishlisted ? 'currentColor' : 'none' }}" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="{{ $wishlisted ? '0' : '1.5' }}" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
        </button>

        <!-- Quick View Overlay (appears on hover) -->
        <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
            <a href="{{ route('product',['slug'=> $product->slug ]) }}" class="bg-white text-gray-900 px-4 py-2 rounded-lg font-medium transform translate-y-4 group-hover:translate-y-0 transition-transform duration-300">
                <i class="fas fa-eye mr-2"></i>Quick View
            </a>
        </div>
    </div>

    <!-- Product Details -->
    <div class="p-3 sm:p-4">
        <!-- Category -->
        @if(isset($category_name))
        <div class="text-xs text-primary font-medium mb-1.5 uppercase tracking-wide">{{ $category_name }}</div>
        @endif

        <!-- Product Name -->
        <h4 class="text-sm sm:text-base text-gray-900 font-semibold mb-2 line-clamp-2 min-h-[2.5rem] hover:text-primary transition-colors">
            <a href="{{ route('product',['slug'=> $product->slug ]) }}">{{ $product->name }}</a>
        </h4>

        <!-- Rating & Reviews -->
        <div class="flex items-center gap-2 mb-2">
            <div class="flex items-center">
                @php $rating = $product->rating ?? 4; @endphp
                @for($i = 1; $i <= 5; $i++)
                <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 {{ $i <= $rating ? 'text-warning fill-warning' : 'text-gray-300' }}" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
                @endfor
            </div>
            <span class="text-xs text-gray-500">({{ $product->review_count ?? 0 }})</span>
        </div>

        <!-- Price -->
        <div class="flex items-baseline gap-2 mb-3">
            @if(!empty($product->discount_price) && $product->discount_price > 0)
                <span class="text-primary text-lg sm:text-xl font-bold">${{ number_format($product->discount_price, 2) }}</span>
                <span class="text-gray-400 text-sm line-through">${{ number_format($product->price, 2) }}</span>
            @else
                <span class="text-primary text-lg sm:text-xl font-bold">${{ number_format($product->price, 2) }}</span>
            @endif
        </div>

        <!-- Stock Status / Sold Count -->
        <div class="text-xs text-gray-600 mb-3 flex items-center">
            @if($product->sold_count > 0)
            <i class="fas fa-fire text-warning mr-1"></i>
            <span class="font-medium">{{ $product->sold_count }} sold</span>
            @else
            <i class="fas fa-box text-success mr-1"></i>
            <span class="font-medium">In Stock</span>
            @endif
        </div>

        <!-- Action Buttons -->
        <div class="grid grid-cols-2 gap-2">
            <form class="add-to-cart-form">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="quantity" value="1">
                <button
                    type="submit"
                    class="bg-primary hover:bg-primary-dark text-white text-xs sm:text-sm font-semibold py-2.5 px-3 rounded-lg flex items-center justify-center w-full transition-all hover:shadow-lg transform hover:-translate-y-0.5"
                >
                    <i class="fas fa-cart-plus mr-1.5"></i>
                    <span class="hidden sm:inline">Add</span>
                </button>
            </form>
            <form action="{{ route('buy.now') }}" method="POST" class="buy-now-form">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="quantity" value="1">
                <button
                    type="submit"
                    class="bg-gray-900 hover:bg-gray-800 text-white text-xs sm:text-sm font-semibold py-2.5 px-3 rounded-lg flex items-center justify-center w-full transition-all hover:shadow-lg transform hover:-translate-y-0.5"
                >
                    <i class="fas fa-shopping-bag mr-1.5"></i>
                    <span class="hidden sm:inline">Buy</span>
                </button>
            </form>
        </div>
    </div>
</div>
