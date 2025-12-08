@extends('frontend_view.layouts.home')

@section('content')
<div class="container mx-auto px-4 py-12">
    <div class="max-w-3xl mx-auto text-center">
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6" role="alert">
            <i class="fas fa-check-circle fa-2x mb-3"></i>
            <h1 class="text-3xl font-bold mb-2">Order Placed Successfully!</h1>
            <p class="text-lg">Your order number is: <strong>{{ $order }}</strong></p>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <p class="mb-4">Thank you for your purchase. We've sent a confirmation email to your registered address.</p>
            <p class="mb-6">You can track your order status in your account dashboard.</p>
            
            <div class="flex justify-center space-x-4">
                <a href="{{ route('home') }}" 
                   class="px-6 py-2 bg-primary text-white rounded hover:bg-secondary">
                    Continue Shopping
                </a>
                <a href="{{ route('account.orders') }}" 
                   class="px-6 py-2 border border-primary text-primary rounded hover:bg-gray-100">
                    View My Orders
                </a>
            </div>
        </div>
    </div>
</div>
@endsection