<section class="py-12 md:py-16 lg:py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-8 md:mb-12">
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900 mb-3">Shop by Category</h2>
            <div class="w-20 h-1 bg-gradient-to-r from-primary to-secondary rounded mx-auto"></div>
            <p class="text-gray-600 mt-4 max-w-2xl mx-auto">Browse through our diverse collection of products</p>
        </div>



        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3 sm:gap-4 md:gap-6">
            @foreach($allCategories as $category)
            <a href="{{ route('category', $category->slug) }}" class="group">
                <div class="relative bg-white rounded-xl overflow-hidden shadow-md hover:shadow-xl transition-all duration-300 hover:-translate-y-2 border border-gray-100 hover:border-primary/30">
                    <div class="aspect-square overflow-hidden bg-gray-50">
                        @php
                            $fallbackImage = 'https://images.unsplash.com/photo-1555774698-0b77e0d5fac6?ixlib=rb-1.2.1&auto=format&fit=crop&w=500&q=60';
                            $imageSrc = $fallbackImage;

                            if (!empty($category->image) && \Illuminate\Support\Facades\Storage::disk('public')->exists($category->image)) {
                                $imageSrc = asset('storage/' . $category->image);
                            } elseif (!empty($category->image) && filter_var($category->image, FILTER_VALIDATE_URL)) {
                                $imageSrc = $category->image;
                            } elseif (!empty($category->image_url) && filter_var($category->image_url, FILTER_VALIDATE_URL)) {
                                $imageSrc = $category->image_url;
                            }
                        @endphp
                        <img
                            class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
                            src="{{ $imageSrc }}"
                            alt="{{ $category->name }}"
                            loading="lazy"
                        >
                    </div>
                    <div class="p-3 text-center">
                        <h3 class="text-sm sm:text-base font-semibold text-gray-900 group-hover:text-primary transition-colors line-clamp-1">{{ $category->name }}</h3>
                    </div>
                    @if($category->is_featured)
                    <div class="absolute top-2 right-2 bg-gradient-to-r from-warning to-danger text-white text-xs font-bold px-2 py-1 rounded-full shadow-lg">
                        <i class="fas fa-star mr-1"></i>Popular
                    </div>
                    @endif
                </div>
            </a>
            @endforeach
        </div>



    </div>
</section>
