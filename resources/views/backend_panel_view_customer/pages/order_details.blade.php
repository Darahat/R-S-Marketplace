@extends('backend_panel_view_customer.layouts.customer')

@section('title', 'Order Tracking')

@section('breadcrumbs')
    <nav class="flex" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('customer.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-primary">
                    <i class="fas fa-home mr-2"></i>
                    Home
                </a>
            </li>
            <li class="inline-flex items-center">
                <a href="{{ route('customer.orders') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-primary">
                    <i class="fas fa-shopping-bag mr-2"></i>
                    My Orders
                </a>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Order #{{ $orderData->order_number }}</span>
                </div>
            </li>
        </ol>
    </nav>
@endsection

@section('panel-content')
<div class="bg-white rounded-lg shadow-sm p-6 mb-6">
    <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Order #{{ $orderData->order_number }}</h1>
            <p class="text-gray-600">Placed on {{ \Carbon\Carbon::parse($orderData->created_at)->format('F j, Y') }}</p>
        </div>
        <div class="mt-4 md:mt-0">
            <span class="px-3 py-1 rounded-full text-sm font-medium
                @if($orderData->order_status === 'shipped') bg-blue-100 text-blue-800
                @elseif($orderData->order_status === 'delivered') bg-green-100 text-green-800
                @else bg-yellow-100 text-yellow-800 @endif">
                {{ ucfirst($orderData->order_status) }}
            </span>
        </div>
    </div>

    <!-- Order Progress Tracker -->
    <div class="mb-8">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Order Status</h2>
        <div class="relative">
            <div class="flex justify-between items-center">
                @foreach($progress_steps as $step)
                <div class="flex flex-col items-center relative z-10" style="width: calc(100%/{{ count($progress_steps) }});">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center mb-2
                        @if($step['completed']) bg-green-500 text-white
                        @elseif($step['is_current']) bg-blue-500 text-white
                        @else bg-gray-200 text-gray-600 @endif">
                        @if($step['completed'])
                        <i class="fas fa-check"></i>
                        @else
                        {{ $loop->iteration }}
                        @endif
                    </div>
                    <span class="text-sm font-medium text-center @if($step['is_current']) text-blue-600 @elseif($step['completed']) text-green-600 @else text-gray-500 @endif">
                        {{ $step['label'] }}
                    </span>
                    @if($step['is_current'])
                    <span class="text-xs text-blue-600 mt-1">Current Status</span>
                    @endif
                </div>
                @endforeach
            </div>
            <div class="absolute top-6 left-0 right-0 h-1 bg-gray-200 z-0">
                <div class="h-full bg-green-500" style="width:
                    @if($orderData->order_status === 'packaged') 33%
                    @elseif($orderData->order_status === 'shipped') 66%
                    @elseif($orderData->order_status === 'delivered') 100%
                    @else 0% @endif">
                </div>
            </div>
        </div>
    </div>

    <!-- Order Summary -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="font-medium text-gray-800 mb-2">Shipping Address</h3>
            <p class="text-gray-600">John Doe</p>
            <p class="text-gray-600">123 Main Street</p>
            <p class="text-gray-600">New York, NY 10001</p>
            <p class="text-gray-600">United States</p>
            <p class="text-gray-600">Phone: (123) 456-7890</p>
        </div>

        <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="font-medium text-gray-800 mb-2">Billing Address</h3>
            <p class="text-gray-600">John Doe</p>
            <p class="text-gray-600">123 Main Street</p>
            <p class="text-gray-600">New York, NY 10001</p>
            <p class="text-gray-600">United States</p>
        </div>

        <div class="bg-gray-50 p-4 rounded-lg">
            <h3 class="font-medium text-gray-800 mb-2">Payment Summary</h3>
            <div class="flex justify-between py-2 border-b border-gray-200">
                <span class="text-gray-600">Subtotal:</span>
                <span class="text-gray-800">${{ number_format($orderData->subtotal, 2) }}</span>
            </div>
            <div class="flex justify-between py-2 border-b border-gray-200">
                <span class="text-gray-600">Discount:</span>
                <span class="text-green-600">-${{ number_format($orderData->discount, 2) }}</span>
            </div>
            <div class="flex justify-between py-2 font-medium">
                <span class="text-gray-800">Total:</span>
                <span class="text-gray-800">${{ number_format($orderData->total_amount, 2) }}</span>
            </div>
            <div class="mt-3 pt-3 border-t border-gray-200">
                <span class="text-gray-600">Payment Method:</span>
                <span class="text-gray-800 block">Credit Card (VISA ****4242)</span>
            </div>
        </div>
    </div>

    <!-- Order Items -->
    <div>
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Order Items</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <!-- Sample product row - replace with dynamic data -->
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <img class="h-10 w-10 rounded" src="https://via.placeholder.com/80" alt="">
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">Premium Headphones</div>
                                    <div class="text-sm text-gray-500">SKU: HD-2023</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$99.99</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">1</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">$99.99</td>
                    </tr>
                    <!-- End sample row -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Help Section -->
    <div class="mt-8 pt-6 border-t border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Need Help?</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="font-medium text-gray-800 mb-2"><i class="fas fa-question-circle mr-2 text-blue-500"></i>Order Questions</h3>
                <p class="text-gray-600 text-sm mb-2">If you have any questions about your order, our customer service team is happy to help.</p>
                <a href="#" class="text-blue-600 text-sm hover:underline">Contact Customer Support</a>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <h3 class="font-medium text-gray-800 mb-2"><i class="fas fa-undo mr-2 text-blue-500"></i>Returns & Exchanges</h3>
                <p class="text-gray-600 text-sm mb-2">Not satisfied with your purchase? Learn about our easy return policy.</p>
                <a href="#" class="text-blue-600 text-sm hover:underline">View Return Policy</a>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .progress-step.active {
        border-color: #3B82F6;
        background-color: #3B82F6;
        color: white;
    }
    .progress-step.completed {
        border-color: #10B981;
        background-color: #10B981;
        color: white;
    }
</style>
@endpush

@push('scripts')
<script>
    // You can add interactive elements here if needed
    document.addEventListener('DOMContentLoaded', function() {
        // Example: Track package button functionality
        const trackPackageBtn = document.getElementById('track-package');
        if(trackPackageBtn) {
            trackPackageBtn.addEventListener('click', function() {
                // Implement package tracking functionality
                console.log('Tracking package for order #{{ $orderData->order_number }}');
            });
        }
    });
</script>
@endpush
@endsection
