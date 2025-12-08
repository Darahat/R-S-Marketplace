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
    <section class="bg-gradient-to-r from-primary to-secondary text-white py-16">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl md:text-4xl font-bold mb-4">Stay Updated</h2>
            <p class="text-xl max-w-2xl mx-auto mb-8">Subscribe to our newsletter for the latest tech news, exclusive deals, and product launches.</p>
            <form class="max-w-xl mx-auto flex flex-col sm:flex-row gap-2">
                <input type="email" placeholder="Your email address" class="flex-grow px-6 py-3 rounded-full text-dark focus:outline-none focus:ring-2 focus:ring-accent">
                <button type="submit" class="bg-accent hover:bg-success text-white font-semibold px-8 py-3 rounded-full transition whitespace-nowrap">
                    Subscribe <i class="fas fa-paper-plane ml-2"></i>
                </button>
            </form>
        </div>
    </section>

   
@endsection