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



@endsection
