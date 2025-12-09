@extends('frontend_view.layouts.home')
@section('content')

    <!-- Hero Section -->
    @include('frontend_view.components.sections.heroSection')

    <!-- Categories Section -->
    @include('frontend_view.components.sections.categorySection')

    <!-- Latest Products -->
    @include('frontend_view.components.sections.latestProductsSection')

    <!-- Best Selling Products -->
    @include('frontend_view.components.sections.bestSellingSection')

    <!-- Discount Products -->
    @include('frontend_view.components.sections.discountSection')

    <!-- Regular Products -->
    @include('frontend_view.components.sections.regularProductsSection')

    <!-- Suggested Products -->
    @include('frontend_view.components.sections.suggestedProductsSection')

    <!-- Newsletter -->
    <section class="bg-gradient-to-r from-primary via-secondary to-primary text-white py-12 md:py-16 lg:py-20 relative overflow-hidden">
        <div class="absolute inset-0 opacity-10">
            <div class="absolute inset-0" style="background-image: radial-gradient(circle, white 1px, transparent 1px); background-size: 20px 20px;"></div>
        </div>
        <div class="container mx-auto px-4 text-center relative z-10">
            <div class="max-w-3xl mx-auto">
                <div class="mb-6">
                    <i class="fas fa-envelope-open-text text-5xl mb-4 opacity-90"></i>
                </div>
                <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold mb-4">Stay Updated</h2>
                <p class="text-base sm:text-lg md:text-xl max-w-2xl mx-auto mb-8 text-white/90">
                    Subscribe to our newsletter for the latest tech news, exclusive deals, and product launches.
                </p>
                <form class="max-w-xl mx-auto">
                    <div class="flex flex-col sm:flex-row gap-3">
                        <input
                            type="email"
                            placeholder="Your email address"
                            class="flex-grow px-6 py-3 sm:py-4 rounded-full text-gray-900 focus:outline-none focus:ring-4 focus:ring-white/30 shadow-lg"
                            required
                        >
                        <button
                            type="submit"
                            class="bg-white text-primary hover:bg-yellow-300 hover:text-gray-900 font-bold px-8 py-3 sm:py-4 rounded-full transition-all hover:-translate-y-1 hover:shadow-2xl whitespace-nowrap"
                        >
                            Subscribe <i class="fas fa-paper-plane ml-2"></i>
                        </button>
                    </div>
                    <p class="text-xs sm:text-sm text-white/70 mt-4">
                        <i class="fas fa-lock mr-1"></i>We respect your privacy. Unsubscribe anytime.
                    </p>
                </form>
            </div>
        </div>
    </section>


@endsection
