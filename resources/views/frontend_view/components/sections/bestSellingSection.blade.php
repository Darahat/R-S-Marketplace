<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">
        <div class="text-center mb-12 relative">
            <h2 class="text-3xl md:text-4xl font-bold text-dark inline-block">Best Selling</h2>
            <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-20 h-1 bg-gradient-to-r from-primary to-accent rounded"></div>
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-3 lg:grid-cols-6 gap-8">
            @foreach($bestSellingProducts as $product)
                @include('frontend_view.components.cards.productCard', ['product' => $product])
            @endforeach
        </div>
    </div>
</section>