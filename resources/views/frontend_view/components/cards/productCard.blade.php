<div class="group relative bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-md transition-all duration-300 border border-gray-100 hover:border-primary/20">
    <!-- Product Image -->
    <div class="relative pt-[100%] bg-gray-50 overflow-hidden">
        <a href="{{ route('product',['slug'=> $product->slug ]) }}"><img 
            src="{{ $product->image_url }}" 
            alt="{{ $product->name }}" 
            class="absolute inset-0 w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
            loading="lazy"
        ></a>

        <!-- Badges -->
        <div class="absolute top-3 left-3 flex flex-col gap-2">
            @if($product->is_new)
            <span class="inline-flex items-center bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded-full">New</span>
            @endif
            @if(!empty($product->discount_price) && $product->discount_price > 0)
            <span class="inline-flex items-center bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                {{ number_format((($product->price - $product->discount_price) / $product->price) * 100,2) }}%
            </span>
            @endif
        </div>

        <!-- Wishlist -->
  
        <button 
            class="wishlist-toggle absolute top-3 right-3 p-2 bg-white/90 rounded-full shadow-sm hover:bg-white transition-colors duration-200 group/wishlist"
            data-id="{{ $product->id }}">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 {{ $product->is_wishlisted ? 'text-red-500 fill-red-500' : 'text-gray-400 hover:text-red-500' }}" viewBox="0 0 20 20" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="{{ $product->is_wishlisted ? '0' : '1.5' }}" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
        </button>

        
    </div>

    <!-- Product Details -->
    <div class="p-4">
        <div class="text-xs text-primary font-medium mb-1">{{ $category_name ?? null}}</div>
        <h4 class="text-sm text-gray-900 font-medium mb-1 line-clamp-1 hover:text-primary transition-colors">
            <a href="{{ route('product',['slug'=> $product->slug ]) }}">{{ $product->name }}</a>
        </h4>

        <div class="flex items-center mb-2">
            <div class="flex items-center mr-2">
                @for($i = 1; $i <= 5; $i++)
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 {{ $i <= $product->rating ? 'text-yellow-400' : 'text-gray-300' }}" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                </svg>
                @endfor
            </div>
            <span class="text-xs text-gray-500">({{ $product->review_count ?? 0 }})</span>
        </div>

        <!-- Price -->
        <div class="flex items-center mb-3">
            @if(!empty($product->discount_price) && $product->discount_price > 0)
                <span class="text-primary text-base font-bold">${{ $product->discount_price }}</span>
                <span class="text-gray-400 text-base line-through ml-2">${{ $product->price }}</span>
            @else
                <span class="text-primary text-base font-bold">${{ $product->price }}</span>
            @endif
        </div>

        <div class="text-xs text-gray-500 mb-3">
            @if($product->sold_count > 0)
            {{ $product->sold_count }} sold
            @else
            Just launched
            @endif
        </div>

        <div class="grid grid-cols-2 gap-2">
            <form class="add-to-cart-form">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="quantity" value="1">
                <button type="submit" class="bg-primary hover:bg-primary-dark text-white text-sm font-medium py-2 px-3 rounded-lg flex items-center justify-center w-full">
                    Add
                </button>
            </form>
            <button class="bg-gray-900 hover:bg-gray-800 text-white text-sm font-medium py-2 px-3 rounded-lg flex items-center justify-center">
                Buy Now
            </button>
        </div>
    </div>
</div>


