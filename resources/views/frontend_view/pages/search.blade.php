@extends('frontend_view.layouts.home')
@section('content')

<!-- Search Header -->
<div class="bg-gradient-to-r from-primary to-secondary text-white py-8 md:py-12">
    <div class="container mx-auto px-4">
        <h1 class="text-3xl md:text-4xl font-bold mb-2">
            <i class="fas fa-search mr-2"></i>Search Results
        </h1>
        @if(!empty($query))
            <p class="text-lg opacity-90">Results for "<strong>{{ $query }}</strong>"</p>
        @endif
    </div>
</div>

<!-- Search Results Section -->
<div class="container mx-auto px-4 py-8 md:py-12">
    <form id="filter-form" method="GET" action="{{ route('search') }}" class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Keep search query -->
        <input type="hidden" name="q" value="{{ $query }}">

        <!-- Sidebar Filters -->
        <div class="hidden lg:block">
            <div class="bg-white rounded-lg shadow-md p-6 sticky top-20">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Filters</h3>

                <!-- Price Filter -->
                <div class="mb-6">
                    <h4 class="font-semibold text-gray-700 mb-3">Price Range</h4>
                    <div class="space-y-3">
                        <div>
                            <label class="text-sm text-gray-600">Min Price</label>
                            <input type="number" name="min_price" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                                   placeholder="0" value="{{ request('min_price', '') }}" min="0">
                        </div>
                        <div>
                            <label class="text-sm text-gray-600">Max Price</label>
                            <input type="number" name="max_price" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary"
                                   placeholder="10000" value="{{ request('max_price', '') }}" min="0">
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Category Filter -->
                <div class="mb-6">
                    <h4 class="font-semibold text-gray-700 mb-3">Categories</h4>
                    <div class="space-y-2">
                        @forelse($categories as $category)
                            <label class="flex items-center cursor-pointer hover:text-primary transition">
                                <input type="checkbox" name="categories[]" value="{{ $category->id }}" class="mr-2 rounded text-primary"
                                    {{ in_array($category->id, (array)request('categories', [])) ? 'checked' : '' }}>
                                <span class="text-sm text-gray-700">{{ $category->name }}</span>
                            </label>
                        @empty
                            <p class="text-sm text-gray-500">No categories available</p>
                        @endforelse
                    </div>
                </div>

                <!-- Filter Button -->
                <button type="submit" class="w-full bg-primary text-white py-2 rounded-lg font-semibold hover:bg-secondary transition">
                    Apply Filters
                </button>
            </div>
        </div>

        <!-- Results Section -->
        <div class="lg:col-span-3">
            @if($products->count() > 0)
                <div class="mb-6 flex items-center justify-between">
                    <p class="text-gray-700 font-medium">
                        Showing <strong>{{ $products->count() }}</strong> results
                        @if(!empty($query))
                            for "<strong>{{ $query }}</strong>"
                        @endif
                    </p>
                    <select class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary">
                        <option>Sort by Relevance</option>
                        <option>Lowest Price</option>
                        <option>Highest Price</option>
                        <option>Newest</option>
                        <option>Best Selling</option>
                    </select>
                </div>

                <!-- Product Grid -->
                <div id="products-container">
                    @include('frontend_view.components.cards.productGrid', ['products' => $products])
                </div>

                <!-- Pagination -->
                <div class="mt-8">
                    {{ $products->links('pagination::bootstrap-5') }}
                </div>
            @else
                <!-- No Results -->
                <div class="bg-white rounded-lg shadow-md p-12 text-center">
                    <div class="mb-4">
                        <i class="fas fa-search text-6xl text-gray-300 mb-4"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-2">No Products Found</h3>
                    <p class="text-gray-600 mb-6">
                        @if(!empty($query))
                            We couldn't find any products matching "<strong>{{ $query }}</strong>".
                            Try searching with different keywords or browse our categories.
                        @else
                            Please enter a search term to find products.
                        @endif
                    </p>
                    <a href="{{ route('home') }}" class="inline-block bg-primary hover:bg-primary-dark text-white font-bold py-3 px-8 rounded-lg transition duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Home
                    </a>
                </div>
            @endif
        </div>
    </form>
</div>

@endsection

@push('scripts')
<script>
    // AJAX pagination
    $(document).on('click', '.pagination a', function(e) {
        e.preventDefault();
        let page = $(this).attr('href').split('page=')[1];
        let query = "{{ $query }}";

        $.ajax({
            url: "{{ route('search') }}?page=" + page + "&q=" + query,
            success: function(data) {
                $('#products-container').html(data);
                $('html, body').animate({scrollTop: 0}, 'slow');
            }
        });
    });

    // Sort functionality
    $('select').on('change', function() {
        // Implement sorting logic here
    });
</script>
@endpush
