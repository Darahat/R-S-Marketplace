@extends('backend_panel_view_customer.layouts.customer')

@section('title', 'Dashboard Overview')

@section('breadcrumbs')
    <nav class="flex" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('customer.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-primary">
                    <i class="fas fa-home mr-2"></i>
                    Home
                </a>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Dashboard</span>
                </div>
            </li>
        </ol>
    </nav>
@endsection

@section('panel-content')
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-gradient-to-r from-primary to-secondary text-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium">Total Orders</p>
                    <p class="text-2xl font-bold">{{$dashboard_data['total_order_count']}}</p>
                </div>
                <i class="fas fa-shopping-bag text-3xl opacity-50"></i>
            </div>
        </div>

        <div class="bg-gradient-to-r from-green-500 to-green-400 text-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium">Completed</p>
                    <p class="text-2xl font-bold">{{$dashboard_data['completed_order_count']}}</p>
                </div>
                <i class="fas fa-check-circle text-3xl opacity-50"></i>
            </div>
        </div>

        <div class="bg-gradient-to-r from-yellow-500 to-yellow-400 text-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium">Pending</p>
                    <p class="text-2xl font-bold">{{$dashboard_data['pending_order_count']}}</p>
                </div>
                <i class="fas fa-clock text-3xl opacity-50"></i>
            </div>
        </div>

        <div class="bg-gradient-to-r from-red-500 to-red-400 text-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium">Cancelled</p>
                    <p class="text-2xl font-bold">{{$dashboard_data['cancelled_order_count']}}</p>
                </div>
                <i class="fas fa-times-circle text-3xl opacity-50"></i>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="mb-8">
        <h3 class="text-lg font-medium text-gray-900 mb-4">Recent Orders</h3>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($dashboard_data['recent_orders'] as $order)
                    @php
                    $statusConfig = [
                        'pending' => [
                            'class' => 'bg-yellow-100 text-yellow-800',
                            'icon' => 'fas fa-clock',
                            'label' => 'Pending'
                        ],
                        'completed' => [
                            'class' => 'bg-green-100 text-green-800',
                            'icon' => 'fas fa-check-circle',
                            'label' => 'Completed'
                        ],
                        'cancelled' => [
                            'class' => 'bg-red-100 text-red-800',
                            'icon' => 'fas fa-times-circle',
                            'label' => 'Cancelled'
                        ],
                        'shipped' => [
                            'class' => 'bg-blue-100 text-blue-800',
                            'icon' => 'fas fa-truck',
                            'label' => 'Shipped'
                        ],
                        'returned' => [
                            'class' => 'bg-purple-100 text-purple-800',
                            'icon' => 'fas fa-undo',
                            'label' => 'Returned'
                        ],
                        'delivered' => [
                            'class' => 'bg-indigo-100 text-indigo-800',
                            'icon' => 'fas fa-box-open',
                            'label' => 'Delivered'
                        ],
                        'processing' => [
                            'class' => 'bg-orange-100 text-orange-800',
                            'icon' => 'fas fa-cog fa-spin',
                            'label' => 'Processing'
                        ]
                    ];

                    $status = strtolower($order->order_status);
                    $config = $statusConfig[$status] ?? [
                        'class' => 'bg-gray-100 text-gray-800',
                        'icon' => 'fas fa-info-circle',
                        'label' => $order->order_status
                    ];
                    @endphp

                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10 bg-primary bg-opacity-10 rounded-full flex items-center justify-center mr-3">
                                    <i class="fas fa-receipt text-primary"></i>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-primary">{{ $order->order_id }}</div>
                                 </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($order->created_at)->format('d M Y') }}</div>
                            <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($order->created_at)->format('h:i A') }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <span class="px-3 py-1 inline-flex items-center text-xs leading-5 font-semibold rounded-full {{ $config['class'] }}">
                                    <i class="{{ $config['icon'] }} mr-1"></i>
                                    {{ $config['label'] }}
                                </span>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">Tk{{ number_format($order->total_amount, 2) }}</div>
                            {{-- <div class="text-xs text-gray-500">{{ $order->payment_method }}</div> --}}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex space-x-2">
                                <a href="{{ route('customer.order_details', $order->order_id) }}"
                                   class="text-primary hover:text-secondary transition-colors"
                                   data-tooltip="View Details">
                                   <i class="fas fa-eye"></i>
                                </a>
                                @if(in_array($status, ['pending', 'processing']))
                                <a href="#"
                                   class="text-red-500 hover:text-red-700 transition-colors"
                                   data-tooltip="Cancel Order"
                                   onclick="return confirm('Are you sure you want to cancel this order?')">
                                   <i class="fas fa-times"></i>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- Your Cart -->
    <div class="mb-8">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Your Cart</h3>
            <a href="{{ route('cart.view') }}" class="text-sm text-primary hover:text-secondary font-medium">
                View All <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        @if($dashboard_data['cart_items']->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
            @foreach($dashboard_data['cart_items'] as $item)
            <div class="bg-white border rounded-lg overflow-hidden hover:shadow-md transition">
                <div class="relative">
                    <img src="{{ asset($item['image']) }}" alt="{{ $item['name'] }}" class="w-full h-32 object-cover">
                    <span class="absolute top-2 left-2 bg-primary text-white text-xs font-bold px-2 py-1 rounded">
                        Qty: {{ $item['quantity'] }}
                    </span>
                </div>
                <div class="p-3">
                    <h4 class="text-sm font-medium text-gray-900 truncate">{{ $item['name'] }}</h4>
                    <p class="text-sm text-primary font-bold mt-1">৳{{ number_format($item['price'], 2) }}</p>
                    <div class="flex gap-2 mt-2">
                        <a href="{{ route('product', $item['slug']) }}" class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-800 text-center py-1 px-2 rounded text-xs transition">
                            View
                        </a>
                        <button onclick="removeFromCart({{ $item['id'] }})" class="flex-1 bg-red-50 hover:bg-red-100 text-red-600 py-1 px-2 rounded text-xs transition">
                            Remove
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="bg-gray-50 border-2 border-dashed rounded-lg p-8 text-center">
            <i class="fas fa-shopping-cart text-4xl text-gray-300 mb-3"></i>
            <p class="text-gray-500">Your cart is empty</p>
            <a href="{{ route('home') }}" class="inline-block mt-3 text-primary hover:text-secondary font-medium text-sm">
                Start Shopping <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        @endif
    </div>

    <!-- Wishlist Preview -->
    <div class="mb-8">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Your Wishlist</h3>
            <a href="{{ route('wishlist.view') }}" class="text-sm text-primary hover:text-secondary font-medium">
                View All <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        @if($dashboard_data['wishlist_items']->count() > 0)
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-4">
            @foreach($dashboard_data['wishlist_items'] as $item)
            <div class="bg-white border rounded-lg overflow-hidden hover:shadow-md transition">
                <img src="{{ asset($item['image']) }}" alt="{{ $item['name'] }}" class="w-full h-32 object-cover">
                <div class="p-3">
                    <h4 class="text-sm font-medium text-gray-900 truncate">{{ $item['name'] }}</h4>
                    <p class="text-sm text-primary font-bold mt-1">৳{{ number_format($item['price'], 2) }}</p>
                    <div class="flex gap-1 mt-2">
                        <button onclick="moveToCart({{ $item['id'] }})" class="flex-1 bg-primary hover:bg-secondary text-white py-1 px-2 rounded text-xs transition">
                            <i class="fas fa-cart-plus"></i>
                        </button>
                        <button onclick="removeFromWishlist({{ $item['id'] }})" class="bg-red-50 hover:bg-red-100 text-red-600 py-1 px-2 rounded text-xs transition">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="bg-gray-50 border-2 border-dashed rounded-lg p-8 text-center">
            <i class="fas fa-heart text-4xl text-gray-300 mb-3"></i>
            <p class="text-gray-500">Your wishlist is empty</p>
            <a href="{{ route('home') }}" class="inline-block mt-3 text-primary hover:text-secondary font-medium text-sm">
                Browse Products <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
function removeFromCart(productId) {
    if (!confirm('Remove this item from your cart?')) return;

    $.ajax({
        url: '{{ route("cart.remove") }}',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            item: productId
        },
        success: function(response) {
            location.reload();
        },
        error: function() {
            alert('Failed to remove item from cart');
        }
    });
}

function removeFromWishlist(productId) {
    if (!confirm('Remove this item from your wishlist?')) return;

    $.ajax({
        url: '{{ route("wishlist.remove") }}',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            product_id: productId
        },
        success: function(response) {
            location.reload();
        },
        error: function() {
            alert('Failed to remove item from wishlist');
        }
    });
}

function moveToCart(productId) {
    $.ajax({
        url: '{{ route("wishlist.moveToCart") }}',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            product_id: productId,
            quantity: 1
        },
        success: function(response) {
            location.reload();
        },
        error: function() {
            alert('Failed to move item to cart');
        }
    });
}
</script>
@endpush
