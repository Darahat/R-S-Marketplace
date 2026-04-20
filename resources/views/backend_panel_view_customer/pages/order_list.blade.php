@extends('backend_panel_view_customer.layouts.customer')

@section('title', 'My Orders')

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
                    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">My Orders</span>
                </div>
            </li>
        </ol>
    </nav>
@endsection

@section('panel-content')
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
        <h1 class="text-xl font-semibold text-gray-800">My Orders</h1>
        <p class="text-sm text-gray-600 mt-1">View and manage your recent orders</p>
    </div>

    <!-- Order Filter Controls -->
    <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between space-y-4 md:space-y-0">
            <div class="flex items-center space-x-4">
                <div>
                    <label for="status-filter" class="block text-sm font-medium text-gray-700 mb-1">Filter by status</label>
                    <select id="status-filter" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md">
                        <option value="all">All Orders</option>
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div>
                    <label for="date-filter" class="block text-sm font-medium text-gray-700 mb-1">Time period</label>
                    <select id="date-filter" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-md">
                        <option value="30">Last 30 days</option>
                        <option value="90">Last 3 months</option>
                        <option value="180">Last 6 months</option>
                        <option value="365">Last year</option>
                        <option value="all">All time</option>
                    </select>
                </div>
            </div>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input type="text" id="order-search" class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:placeholder-gray-400 focus:ring-1 focus:ring-primary-500 focus:border-primary-500 sm:text-sm" placeholder="Search orders...">
            </div>
        </div>
    </div>

    <!-- Orders List -->
    <div class="divide-y divide-gray-200">
        @forelse ($orderData as $order)
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
        <div class="px-6 py-4 hover:bg-gray-50 transition-colors duration-150">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div class="mb-4 md:mb-0">
                    <div class="flex items-center space-x-4">
                        <div>
                            <span class="text-sm font-medium text-gray-500">Order #</span>
                            <a href="{{ route('customer.order_details', ['orderNumber' => $order->order_number]) }}" class="text-base font-semibold text-primary-600 hover:text-primary-800">{{ $order->order_number }}</a>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500">Placed on</span>
                            <span class="text-sm text-gray-600">{{ \Carbon\Carbon::parse($order->created_at)->format('d M Y') }}</span>
                        </div>
                        <div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 {{ $config['class'] }}">
                                    <i class="{{ $config['icon'] }} mr-1"></i>
                                    {{ $config['label'] }}
                                {{$order->order_status}}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <span class="text-sm font-medium text-gray-500 block">Total</span>
                        <span class="text-base font-semibold text-gray-900">৳{{ number_format($order->total_amount, 2) }}</span>
                    </div>
                    <div>
                        <a href="{{ route('customer.order_details', ['orderNumber' => $order->order_number]) }}" class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                            View Details
                        </a>
                    </div>
                </div>
            </div>

            <!-- Order Items Preview -->
            <div class="mt-4">
                <div class="flex items-center justify-between gap-4 flex-wrap">
                    <div class="flex -space-x-2 overflow-hidden">
                        @forelse($order->items->take(3) as $item)
                            @php
                                $product = $item->product;
                                $productImage = $product && $product->image
                                    ? (filter_var($product->image, FILTER_VALIDATE_URL) ? $product->image : asset('storage/' . $product->image))
                                    : asset('images/no-image.svg');
                            @endphp
                            <img
                                class="inline-block h-12 w-12 rounded-full ring-2 ring-white object-cover"
                                src="{{ $productImage }}"
                                alt="{{ $product?->name ?? 'Product image' }}"
                                title="{{ $product?->name ?? 'Product' }}"
                                onerror="this.onerror=null;this.src='{{ asset('images/no-image.svg') }}';"
                            >
                        @empty
                            <span class="inline-flex items-center justify-center h-12 px-3 rounded-full bg-gray-100 ring-2 ring-white text-gray-500 text-xs font-medium">
                                No items
                            </span>
                        @endforelse

                        @if($order->items->count() > 3)
                            <span class="inline-flex items-center justify-center h-12 w-12 rounded-full bg-gray-100 ring-2 ring-white text-gray-500 font-medium">
                                +{{ $order->items->count() - 3 }}
                            </span>
                        @endif
                    </div>
                    <div class="text-sm text-gray-500">
                        {{ $order->items->count() }} {{ \Illuminate\Support\Str::plural('item', $order->items->count()) }}
                    </div>
                </div>
            </div>
        </div>

        @empty
        <div class="px-6 py-12 text-center">
            <div class="mx-auto h-14 w-14 rounded-full bg-gray-100 flex items-center justify-center mb-4">
                <i class="fas fa-box-open text-gray-400 text-xl"></i>
            </div>
            <h2 class="text-lg font-semibold text-gray-800">No orders yet</h2>
            <p class="text-sm text-gray-600 mt-2">You have not placed any orders yet. Start browsing products to place your first order.</p>
            <a href="{{ route('home') }}" class="inline-flex items-center mt-4 px-4 py-2 rounded-md bg-primary-600 text-white hover:bg-primary-700">
                Continue Shopping
            </a>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($orderData->hasPages())
    <div class="px-6 py-4 bg-gray-50 border-t border-gray-200 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-sm text-gray-700">
                Showing
                <span class="font-medium">{{ $orderData->firstItem() }}</span>
                to
                <span class="font-medium">{{ $orderData->lastItem() }}</span>
                of
                <span class="font-medium">{{ $orderData->total() }}</span>
                orders
            </p>
        </div>
        <div>
            {{ $orderData->appends(request()->query())->links() }}
        </div>
    </div>
    @endif
</div>

@push('styles')
<style>
    .order-status {
        transition: all 0.2s ease;
    }
    .order-item:hover {
        transform: translateY(-2px);
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const statusFilter = document.getElementById('status-filter');
        const dateFilter = document.getElementById('date-filter');
        const orderSearch = document.getElementById('order-search');

        [statusFilter, dateFilter, orderSearch].forEach(element => {
            element.addEventListener('change', function() {
                console.log('Filtering orders...');
            });
        });
    });
</script>
@endpush
@endsection
