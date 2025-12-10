@extends('frontend_view.layouts.home')
@section('content')

<div class="container mx-auto px-4 py-8">
    <!-- Breadcrumb -->
    <nav class="text-sm mb-6" aria-label="Breadcrumb">
        <ol class="list-reset flex text-gray-600">
            <li><a href="{{ route('home') }}" class="hover:text-primary">Home</a></li>
            <li><span class="mx-2">/</span></li>
            <li><a href="" class="hover:text-primary">{{ $product->category_id }}</a></li>
            <li><span class="mx-2">/</span></li>
            <li class="text-gray-900">{{ $product->name }}</li>
        </ol>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Gallery -->
        <div>
            <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-4">
                <img src="{{ $product->featured_image ?? $product->image_url ?? $product->image ?? 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=800' }}" alt="{{ $product->name }}" class="w-full h-auto object-cover" loading="lazy">
            </div>
            @php
                $imageUrls = !empty($product->image_url) ? explode(',', $product->image_url) : [];
            @endphp
            @if($imageUrls && count($imageUrls) > 1)
            <div class="grid grid-cols-4 gap-2">
                 @foreach($imageUrls as $img)
                 @if(!empty($img))
                <img src="{{ $img }}" alt="{{ $product->name }}" class="w-full h-18 object-cover rounded-lg cursor-pointer hover:ring-2 hover:ring-primary transition">
                @endif
                @endforeach
            </div>
            @endif
        </div>

        <!-- Details -->
        <div>
            <h1 class="text-2xl font-bold text-gray-900 mb-2">{{ $product->name }}</h1>
            <div class="flex items-center mb-4">
                <div class="flex items-center mr-2">
                    @for($i = 1; $i <= 5; $i++)
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 {{ $i <= round($averageRating) ? 'text-yellow-400' : 'text-gray-300' }}" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                        </svg>
                    @endfor
                </div>
                <span class="text-sm text-gray-500">({{ $reviewCount ?? 0 }} reviews)</span>
            </div>

            <div class="text-3xl font-bold text-primary mb-4">


                  @if(!empty($product->discount_price) && $product->discount_price > 0)
                        <span class="text-primary text-3xl font-bold">${{ $product->discount_price }}</span>
                        <span class="text-gray-400 text-3xl line-through ml-2">${{ $product->price }}</span>
                    @else
                        <span class="text-primary text-3xl font-bold">${{ $product->price }}</span>
                    @endif
            </div>

            <p class="text-gray-700 mb-6">{{ $product->description }}</p>

            <form action="{{ route('cart.add') }}" method="POST" class="flex items-center space-x-4 mb-6">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <div>
                    <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                    <input type="number" name="quantity" id="quantity" value="1" min="1" class="w-20 border-gray-300 rounded-md text-center">
                </div>
                <button type="submit" class="bg-primary hover:bg-primary-dark text-white py-2 px-6 rounded-lg font-medium transition">Add to Cart</button>
            </form>

            <form action="{{ route('buy.now') }}" method="POST" class="inline-block">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <input type="hidden" name="quantity" id="buy-now-quantity" value="1">
                <button type="submit" class="bg-gray-900 hover:bg-gray-800 text-white py-2 px-6 rounded-lg font-medium transition">
                    <i class="fas fa-bolt mr-1"></i>Buy Now
                </button>
            </form>

            <!-- Stock & Sold Info -->
            <div class="mt-6 text-sm text-gray-500">
                @php
                    $stockQty = $product->stock ?? $product->stock ?? 0;
                @endphp
                @if($stockQty > 0)
                    In Stock: {{ $stockQty }} units
                @else
                    <span class="text-red-500">Out of Stock</span>
                @endif
                @if($product->sold_count > 0)
                <span class="ml-4">Sold: {{ $product->sold_count }}</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Description & Reviews Tabs -->
    <div class="mt-12 bg-white rounded-xl shadow-sm p-6">
        <div x-data="{ tab: 'description' }" class="space-y-4">
            <nav class="border-b border-gray-200">
                <ul class="flex -mb-px">
                    <li class="mr-6">
                        <a :class="tab === 'description' ? 'border-primary text-primary' : 'border-transparent text-gray-600'"
                            @click.prevent="tab = 'description'" href="#"
                            class="inline-block py-2 px-4 border-b-2 font-medium">Description</a>
                    </li>
                    <li class="mr-6">
                        <a :class="tab === 'reviews' ? 'border-primary text-primary' : 'border-transparent text-gray-600'"
                            @click.prevent="tab = 'reviews'" href="#"
                            class="inline-block py-2 px-4 border-b-2 font-medium">Reviews ({{ $reviewCount ?? 0 }})</a>
                    </li>
                </ul>
            </nav>

            <div x-show="tab === 'description'" class="pt-4">
                {!! nl2br(e($product->description)) !!}
            </div>

            <!-- Reviews Section -->
<div class="mt-10">
    <h3 class="text-lg font-semibold text-gray-900 mb-4">
        Customer Reviews ({{ $reviewCount }}) - Average: {{ $averageRating }}/5
    </h3>

    @if($reviews->isEmpty())
        <p class="text-gray-500">No reviews yet.</p>
    @else
        <div class="space-y-6">
            @foreach($reviews as $review)
                <div class="border-b pb-4">
                    <div class="flex items-center justify-between mb-1">
                        <div class="font-semibold text-sm text-gray-800">
                            {{ $review->user_name }}
                        </div>
                        <div class="flex items-center">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="w-4 h-4 {{ $i <= $review->rating ? 'text-yellow-400' : 'text-gray-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            @endfor
                        </div>
                    </div>
                    <div class="text-gray-600 text-sm">
                        {{ $review->comment }}
                    </div>
                    <div class="text-xs text-gray-400 mt-1">
                        {{ \Carbon\Carbon::parse($review->created_at)->diffForHumans() }}
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

        </div>
    </div>
</div>

@push('scripts')
<script>
    // Sync quantity between add to cart and buy now forms
    $(document).ready(function() {
        $('#quantity').on('input change', function() {
            $('#buy-now-quantity').val($(this).val());
        });
    });
</script>
@endpush
@endsection
