@extends('frontend_view.layouts.home')

@section('content')

<style>

.loading-spinner {
    display: inline-block;
    width: 20px;
    height: 20px;
    border: 2px solid rgba(0,0,0,.1);
    border-radius: 50%;
    border-top-color: #000;
    animation: spin 1s ease-in-out infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>
<div class="container mx-auto px-4 py-8" >
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold mb-6">Your Shopping Cart</h1>
        <div id="cart-view-section">
            @include('frontend_view.pages.cart.cartItems', ['cartItems' => $cartItems, 'total' => $total])       
        </div>
    </div>
</div>
    
@endsection