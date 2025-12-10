@extends('backend_panel_view.layouts.admin')
@section('content')

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-shopping-cart"></i> {{ $page_header }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">{{ $page_header }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <!-- Statistics Cards -->
            <div class="row mb-3" id="statsRow">
                <div class="col-md-3">
                    <div class="card bg-primary text-white shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title mb-0">Total Orders</h6>
                            <h3 class="mb-0" id="totalOrders">-</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-warning text-white shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title mb-0">Pending Orders</h6>
                            <h3 class="mb-0" id="pendingOrders">-</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-info text-white shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title mb-0">Total Revenue</h6>
                            <h3 class="mb-0" id="totalRevenue">-</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card bg-success text-white shadow-sm">
                        <div class="card-body">
                            <h6 class="card-title mb-0">Delivered Orders</h6>
                            <h3 class="mb-0" id="deliveredOrders">-</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters Card -->
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.orders') }}" class="row g-3">
                        <div class="col-md-2">
                            <input type="text" name="search" class="form-control" placeholder="Order # or Customer" value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                @foreach($statuses as $status)
                                    <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                        {{ ucfirst($status) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="payment_status" class="form-select">
                                <option value="">All Payment Status</option>
                                @foreach($paymentStatuses as $pStatus)
                                    <option value="{{ $pStatus }}" {{ request('payment_status') == $pStatus ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $pStatus)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="payment_method" class="form-select">
                                <option value="">All Methods</option>
                                @foreach($paymentMethods as $method)
                                    <option value="{{ $method }}" {{ request('payment_method') == $method ? 'selected' : '' }}>
                                        {{ strtoupper($method) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i> Filter</button>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('admin.orders') }}" class="btn btn-secondary w-100"><i class="fas fa-redo"></i> Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Orders Table -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0"><i class="fas fa-list"></i> All Orders ({{ $orders->total() }})</h3>
                </div>

                <div class="card-body table-responsive p-0">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th width="10%">Order #</th>
                                <th width="12%">Customer</th>
                                <th width="10%" class="text-center">Amount</th>
                                <th width="12%" class="text-center">Status</th>
                                <th width="12%" class="text-center">Payment</th>
                                <th width="10%" class="text-center">Method</th>
                                <th width="14%">Date</th>
                                <th width="14%" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                                <tr>
                                    <td>
                                        <strong>{{ $order->order_number }}</strong>
                                    </td>
                                    <td>
                                        {{ $order->user->name }}<br>
                                        <small class="text-muted">{{ $order->user->email }}</small>
                                    </td>
                                    <td class="text-center">
                                        <strong>৳{{ number_format($order->total, 2) }}</strong>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge
                                            {{ $order->status == 'pending' ? 'bg-secondary' : '' }}
                                            {{ $order->status == 'confirmed' ? 'bg-info' : '' }}
                                            {{ $order->status == 'processing' ? 'bg-warning' : '' }}
                                            {{ $order->status == 'shipped' ? 'bg-primary' : '' }}
                                            {{ $order->status == 'delivered' ? 'bg-success' : '' }}
                                            {{ $order->status == 'cancelled' ? 'bg-danger' : '' }}
                                            {{ $order->status == 'returned' ? 'bg-dark' : '' }}
                                        ">
                                            {{ ucfirst($order->status) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge
                                            {{ $order->payment_status == 'unpaid' ? 'bg-danger' : '' }}
                                            {{ $order->payment_status == 'paid' ? 'bg-success' : '' }}
                                            {{ $order->payment_status == 'failed' ? 'bg-dark' : '' }}
                                        ">
                                            {{ ucfirst($order->payment_status) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary">{{ strtoupper($order->payment_method) }}</span>
                                    </td>
                                    <td>
                                        <small>{{ $order->created_at->format('M d, Y') }}</small><br>
                                        <small class="text-muted">{{ $order->created_at->format('h:i A') }}</small>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('admin.orders.show', $order->id) }}"
                                               class="btn btn-info"
                                               data-bs-toggle="tooltip"
                                               title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.orders.print', $order->id) }}"
                                               class="btn btn-success"
                                               target="_blank"
                                               data-bs-toggle="tooltip"
                                               title="Print Invoice">
                                                <i class="fas fa-print"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <p>No orders found.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($orders->hasPages())
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }} of {{ $orders->total() }} orders
                        </div>
                        <nav>
                            {{ $orders->links('pagination::bootstrap-5') }}
                        </nav>
                    </div>
                </div>
                @endif
            </div>

        </div>
    </section>
</div>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });

    // Load statistics
    loadStatistics();

    function loadStatistics() {
        $.ajax({
            url: '{{ route("admin.orders.statistics") }}',
            type: 'GET',
            success: function(data) {
                $('#totalOrders').text(data.total_orders);
                $('#pendingOrders').text(data.pending_orders);
                $('#totalRevenue').text('৳' + Number(data.total_revenue).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                $('#deliveredOrders').text(data.delivered_orders);
            }
        });
    }
});
</script>
@endpush

@endsection
