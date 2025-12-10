@extends('frontend_view.layouts.home')

@section('title', $data['title'])

@section('content')
<!-- Breadcrumb -->
<div class="bg-gray-100 py-4">
    <div class="container mx-auto px-4">
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                <li class="inline-flex items-center">
                    <a href="{{ route('home') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-primary">
                        <i class="fas fa-home mr-2"></i>
                        Home
                    </a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Wishlist</span>
                    </div>
                </li>
            </ol>
        </nav>
    </div>
</div>

<!-- Wishlist Content -->
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">My Wishlist</h1>
        <p class="text-gray-600 mt-2">{{ count($wishlistItems) }} item(s) in your wishlist</p>
    </div>

    @if(count($wishlistItems) > 0)
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6">
        @foreach($wishlistItems as $item)
        <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300 relative" data-product-id="{{ $item['id'] }}">
            <!-- Wishlist Remove Button -->
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
                    <span class="text-xs text-green-600 font-medium">In Stock</span>
                    @else
                    <span class="text-xs text-red-600 font-medium">Out of Stock</span>
                    @endif
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-2">
                    @if(($item['stock'] ?? 0) > 0)
                    <button
                        onclick="moveToCart({{ $item['id'] }})"
                        class="flex-1 bg-primary hover:bg-secondary text-white py-2 px-3 rounded-lg text-sm font-medium transition"
                    >
                        <i class="fas fa-shopping-cart mr-1"></i> Add to Cart
                    </button>
                    @else
                    <button
                        disabled
                        class="flex-1 bg-gray-300 text-gray-500 py-2 px-3 rounded-lg text-sm font-medium cursor-not-allowed"
                    >
                        Out of Stock
                    </button>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @else
    <!-- Empty Wishlist -->
    <div class="text-center py-16">
        <div class="inline-flex items-center justify-center w-24 h-24 bg-gray-100 rounded-full mb-4">
            <i class="fas fa-heart text-4xl text-gray-400"></i>
        </div>
        <h2 class="text-2xl font-semibold text-gray-900 mb-2">Your Wishlist is Empty</h2>
        <p class="text-gray-600 mb-6">Save your favorite items for later!</p>
        <a href="{{ route('home') }}" class="inline-flex items-center px-6 py-3 bg-primary hover:bg-secondary text-white font-medium rounded-lg transition">
            <i class="fas fa-shopping-bag mr-2"></i>
            Continue Shopping
        </a>
    </div>
    @endif
</div>

@push('scripts')
<script>
function removeFromWishlist(productId) {
    if (!confirm('Remove this item from your wishlist?')) {
        return;
    }

    $.ajax({
        url: '{{ route("wishlist.remove") }}',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            product_id: productId
        },
        success: function(response) {
            // Remove the product card from DOM
            $(`[data-product-id="${productId}"]`).fadeOut(300, function() {
                $(this).remove();

                // Update wishlist count
                $('#wishlist-count').text(response.count);

                // Check if wishlist is now empty
                if (response.count === 0) {
                    location.reload();
                }
            });

            // Show success message
            showToast('success', 'Item removed from wishlist');
        },
        error: function() {
            showToast('error', 'Failed to remove item from wishlist');
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
            // Remove from wishlist display
            $(`[data-product-id="${productId}"]`).fadeOut(300, function() {
                $(this).remove();
            });

            // Update counts
            $('#wishlist-count').text(response.count || 0);
            $('#cart-count').text(response.cartQuantity || 0);

            showToast('success', 'Item moved to cart successfully');
        },
        error: function() {
            showToast('error', 'Failed to move item to cart');
        }
    });
}

function showToast(type, message) {
    const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
    const toast = $(`
        <div class="fixed top-20 right-4 ${bgColor} text-white px-6 py-3 rounded-lg shadow-lg z-50 animate-slide-in">
            <div class="flex items-center">
                <i class="fas fa-${type === 'success' ? 'check' : 'exclamation'}-circle mr-2"></i>
                <span>${message}</span>
            </div>
        </div>
    `);

    $('body').append(toast);

    setTimeout(() => {
        toast.fadeOut(300, function() {
            $(this).remove();
        });
    }, 3000);
}
</script>
@endpush
@endsection
