@extends('backend_panel_view_customer.layouts.customer')

@section('title', 'My Wishlist')

@section('breadcrumbs')
    <nav class="flex" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('customer.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-primary">
                    <i class="fas fa-home mr-2"></i>
                    Home
                </a>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Wishlist</span>
                </div>
            </li>
        </ol>
    </nav>
@endsection

@section('panel-content')
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <div>
            <h1 class="text-xl font-semibold text-gray-800">My Wishlist</h1>
            <p class="text-sm text-gray-600 mt-1">
                <span id="wishlist-items-count">{{ count($wishlistItems) }}</span> item(s) saved
            </p>
        </div>
        <a href="{{ route('home') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm bg-primary hover:bg-primary-dark text-white transition">
            <i class="fas fa-shopping-bag mr-2"></i> Continue Shopping
        </a>
    </div>

    @if(count($wishlistItems) > 0)
    <div class="p-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($wishlistItems as $item)
            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden hover:shadow-lg transition-shadow duration-300 relative" data-product-id="{{ $item['id'] }}">
                <!-- Remove Button -->
                <button
                    onclick="removeFromWishlist({{ $item['id'] }})"
                    class="absolute top-2 right-2 bg-white rounded-full p-2 shadow-md hover:bg-red-50 transition z-10"
                    title="Remove from wishlist"
                >
                    <i class="fas fa-times text-red-500"></i>
                </button>

                <!-- Product Image -->
                <div class="relative overflow-hidden bg-gray-100 h-48">
                    <img
                        src="{{ asset($item['image']) }}"
                        alt="{{ $item['name'] }}"
                        class="w-full h-full object-cover hover:scale-110 transition-transform duration-300"
                    >
                </div>

                <!-- Product Details -->
                <div class="p-4">
                    <h3 class="text-sm font-medium text-gray-900 mb-2 line-clamp-2 h-10">
                        {{ $item['name'] }}
                    </h3>

                    <div class="flex items-center justify-between mb-3">
                        <span class="text-lg font-bold text-primary">
                            ৳{{ number_format($item['price'], 2) }}
                        </span>
                        @if(($item['stock'] ?? 0) > 0)
                            <span class="text-xs text-green-600 font-medium bg-green-50 px-2 py-1 rounded">In Stock</span>
                        @else
                            <span class="text-xs text-red-600 font-medium bg-red-50 px-2 py-1 rounded">Out of Stock</span>
                        @endif
                    </div>

                    <!-- Action Button -->
                    @if(($item['stock'] ?? 0) > 0)
                    <button
                        onclick="moveToCart({{ $item['id'] }})"
                        class="w-full bg-primary hover:bg-primary-dark text-white py-2 px-3 rounded-lg text-sm font-medium transition"
                    >
                        <i class="fas fa-shopping-cart mr-1"></i> Move to Cart
                    </button>
                    @else
                    <button
                        disabled
                        class="w-full bg-gray-300 text-gray-500 py-2 px-3 rounded-lg text-sm font-medium cursor-not-allowed"
                    >
                        Out of Stock
                    </button>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @else
    <!-- Empty Wishlist -->
    <div class="text-center py-16">
        <div class="inline-flex items-center justify-center w-24 h-24 bg-gray-100 rounded-full mb-4">
            <i class="fas fa-heart text-4xl text-gray-400"></i>
        </div>
        <h2 class="text-2xl font-semibold text-gray-900 mb-2">Your Wishlist is Empty</h2>
        <p class="text-gray-600 mb-6">Save your favorite items for later!</p>
        <a href="{{ route('home') }}" class="inline-flex items-center px-6 py-3 bg-primary hover:bg-primary-dark text-white font-medium rounded-lg transition">
            <i class="fas fa-shopping-bag mr-2"></i>
            Continue Shopping
        </a>
    </div>
    @endif
</div>

@push('scripts')
<script>
function removeFromWishlist(productId) {
    if (!confirm('Remove this item from your wishlist?')) return;

    $.ajax({
        url: '{{ route("wishlist.remove") }}',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            product_id: productId
        },
        success: function(response) {
            $(`[data-product-id="${productId}"]`).fadeOut(300, function() {
                $(this).remove();
                $('#wishlist-items-count').text(response.count);
                if (response.count === 0) location.reload();
            });
            showToast('success', 'Item removed from wishlist');
        },
        error: function() {
            showToast('error', 'Failed to remove item');
        }
    });
}

function moveToCart(productId) {
    $.ajax({
        url: '{{ route("wishlist.moveToCart") }}',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            product_id: productId,
            quantity: 1
        },
        success: function(response) {
            $(`[data-product-id="${productId}"]`).fadeOut(300, function() {
                $(this).remove();
                const wishlistCount = response.wishlistCount || 0;
                $('#wishlist-items-count').text(wishlistCount);
                if (wishlistCount === 0) location.reload();
            });
            showToast('success', 'Item moved to cart');
        },
        error: function() {
            showToast('error', 'Failed to move item to cart');
        }
    });
}
</script>
@endpush
@endsection
