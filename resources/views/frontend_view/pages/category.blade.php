@extends('frontend_view.layouts.home')
@section('content')

<div class="flex flex-col md:flex-row gap-6 container mx-auto px-4 py-8">

    <!-- Filter Section -->
    <div class="w-full md:w-72 flex-shrink-0">
        <div class="bg-white rounded-xl shadow-sm p-5 sticky top-4">
            <div class="flex justify-between items-center mb-6">
                <h3 class="font-bold text-lg">Filters</h3>
                <a href="" class="text-sm text-primary hover:text-primary-dark">Clear All</a>
            </div>

            <!-- Price Range Filter -->
            <div class="mb-6">
                <h4 class="font-medium text-gray-900 mb-3 flex justify-between items-center">
                    <span>Price Range</span>
                </h4>
                <div class="space-y-3">
                    <div class="flex items-center">
                        <input id="price-all" name="price" type="radio" value="" class="h-4 w-4 text-primary focus:ring-primary border-gray-300" {{ request('price') == '' ? 'checked' : '' }}>
                        <label for="price-all" class="ml-2 text-sm text-gray-700">All Prices</label>
                    </div>
                    <div class="flex items-center">
                        <input id="price-1" name="price" type="radio" value="price-1" class="h-4 w-4 text-primary focus:ring-primary border-gray-300" {{ request('price') == 'price-1' ? 'checked' : '' }}>
                        <label for="price-1" class="ml-2 text-sm text-gray-700">$0 - $25</label>
                    </div>
                    <div class="flex items-center">
                        <input id="price-2" name="price" type="radio" value="price-2" class="h-4 w-4 text-primary focus:ring-primary border-gray-300" {{ request('price') == 'price-2' ? 'checked' : '' }}>
                        <label for="price-2" class="ml-2 text-sm text-gray-700">$25 - $50</label>
                    </div>
                    <div class="flex items-center">
                        <input id="price-3" name="price" type="radio" value="price-3" class="h-4 w-4 text-primary focus:ring-primary border-gray-300" {{ request('price') == 'price-3' ? 'checked' : '' }}>
                        <label for="price-3" class="ml-2 text-sm text-gray-700">$50 - $100</label>
                    </div>
                </div>
            </div>

           
            <!-- Brands Filter -->
            <div class="mb-6" id="brand-filters">
                <h4 class="font-medium text-gray-900 mb-3">Brands</h4>
                <div class="space-y-2">
                    @php
                        $selectedBrands = explode(',', request()->input('brands', ''));
                    @endphp
                    @foreach($brands as $brand)
                        <div class="flex items-center">
                        <input 
                            id="brand-{{ $brand->id }}" 
                            name="brands" 
                            value="{{ $brand->id }}" 
                            type="checkbox"
                            {{ in_array($brand->id, $selectedBrands) ? 'checked' : '' }}
                            class="brand-checkbox h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded"
                        />

                            <label for="brand-{{ $brand->id }}" class="ml-2 text-sm text-gray-700">
                                {{ $brand->name }} <span class="text-gray-400 ml-1">(0)</span>
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>


            <!-- Rating Filter -->
            <div class="mb-6">
                <h4 class="font-medium text-gray-900 mb-3">Customer Reviews</h4>
                <div class="space-y-2">
                    @for($i = 5; $i >= 1; $i--)
                        <div class="flex items-center">
                            <input id="rating-{{ $i }}" name="ratings[]" value="{{ $i }}" type="checkbox"
                                {{ in_array($i, request()->input('ratings', [])) ? 'checked' : '' }}
                                class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                            <label for="rating-{{ $i }}" class="ml-2 text-sm text-gray-700 flex items-center">
                                @for($j = 1; $j <= 5; $j++)
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 {{ $j <= $i ? 'text-yellow-400' : 'text-gray-300' }}" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                    </svg>
                                @endfor
                            </label>
                        </div>
                    @endfor
                </div>
            </div>

           
        </div>
    </div>



    <!-- Product Listing Section -->
    <div class="flex-1">
    <h2 class="text-3xl md:text-4xl font-bold text-dark inline-block">{{$category_name}}</h2>
        <!-- Sorting and View Options -->
        <div class="bg-white rounded-xl shadow-sm p-4 mb-6 flex flex-col sm:flex-row justify-between items-center">
            <div class="text-sm text-gray-600 mb-3 sm:mb-0">
                Showing <span class="font-medium">{{$products->count()}}</span> products
            </div>
            <div class="flex items-center space-x-4">
                <div class="flex items-center">
                    <label for="sort" class="mr-2 text-sm text-gray-600">Sort by:</label>
                    <select id="sort" class="border-0 py-1 pl-2 pr-8 text-sm rounded-lg focus:ring-primary focus:border-primary">
                        <option>Featured</option>
                        <option>Price: Low to High</option>
                        <option>Price: High to Low</option>
                        <option>Newest Arrivals</option>
                        <option>Best Selling</option>
                        <option>Highest Rated</option>
                    </select>
                </div>
                <div class="flex items-center space-x-1">
                    <button class="p-2 rounded-md hover:bg-gray-100 text-gray-500 hover:text-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <button class="p-2 rounded-md hover:bg-gray-100 text-gray-500 hover:text-gray-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        <!-- Product Grid -->
        <div id="product-container" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($products as $product)
                @include('frontend_view.components.cards.productCard', ['product' => $product, 'category_name' => $category_name])
            @endforeach
        </div>

        <div id="load-more-trigger" class="my-10 text-center">
            @if ($products->hasMorePages())
                <button id="load-more-btn" class="px-4 py-2 bg-primary text-white rounded">Load More</button>
            @endif
        </div>
      
    </div>
</div>
<script>
document.addEventListener("DOMContentLoaded", function () {
    const checkboxes = document.querySelectorAll(".brand-checkbox");

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener("change", function () {
            let selectedBrands = [];

            checkboxes.forEach(cb => {
                if (cb.checked) {
                    selectedBrands.push(cb.value);
                }
            });

            const url = new URL(window.location.href);

            url.searchParams.delete("brands[]");
            url.searchParams.delete("brands");

            if (selectedBrands.length > 0) {
                url.searchParams.set("brands", selectedBrands.join(','));
            }

            window.location.href = url.toString();
        });
    });
});
</script>



@endsection
