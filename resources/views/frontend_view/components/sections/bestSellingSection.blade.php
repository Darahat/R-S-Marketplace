<section id="todays-deals" class="py-12 md:py-16 lg:py-20 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-8 md:mb-12">
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900 mb-3">
                <i class="fas fa-fire text-warning mr-2"></i>Best Selling
            </h2>
            <div class="w-20 h-1 bg-gradient-to-r from-warning to-danger rounded mx-auto"></div>
            <p class="text-gray-600 mt-4">Most popular products loved by customers</p>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3 sm:gap-4 md:gap-6">
            @foreach($bestSellingProducts as $product)
                @include('frontend_view.components.cards.productCard', ['product' => $product])
            @endforeach
        </div>
    </div>
</section>
