<section class="py-12 md:py-16 lg:py-20 bg-white">
    <div class="container mx-auto px-4">
        <div class="text-center mb-8 md:mb-12">
            <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold text-gray-900 mb-3">
                <i class="fas fa-tags text-danger mr-2"></i>Discount Offers
            </h2>
            <div class="w-20 h-1 bg-gradient-to-r from-danger to-warning rounded mx-auto"></div>
            <p class="text-gray-600 mt-4">Save big with our special discounts</p>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3 sm:gap-4 md:gap-6">
            @foreach($discountProducts as $product)
                @include('frontend_view.components.cards.productCard', ['product' => $product])
            @endforeach
        </div>
    </div>
</section>
