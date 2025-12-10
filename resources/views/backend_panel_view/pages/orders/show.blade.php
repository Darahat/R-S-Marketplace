@extends('backend_panel_view.layouts.admin')
@section('content')

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-receipt"></i> {{ $page_header }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.orders') }}">Orders</a></li>
                        <li class="breadcrumb-item active">{{ $order->order_number }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Order Details -->
                <div class="col-lg-8">
                    <!-- Order Header -->
                    <div class="card shadow-sm mb-3">
                        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                            <h3 class="card-title mb-0">{{ $order->order_number }}</h3>
                            <a href="{{ route('admin.orders.print', $order->id) }}" class="btn btn-light btn-sm" target="_blank">
                                <i class="fas fa-print"></i> Print Invoice
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong><i class="fas fa-calendar"></i> Order Date:</strong><br>
                                        {{ $order->created_at->format('F d, Y h:i A') }}
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <p><strong><i class="fas fa-cube"></i> Items:</strong><br>
                                        {{ $order->items->count() }} product(s)
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Information -->
                    <div class="card shadow-sm mb-3">
                        <div class="card-header bg-info text-white">
                            <h3 class="card-title mb-0"><i class="fas fa-user"></i> Customer Information</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <p><strong>Name:</strong> {{ $order->user->name }}</p>
                                    <p><strong>Email:</strong> {{ $order->user->email }}</p>
                                    <p><strong>Phone:</strong> {{ $order->user->phone ?? 'N/A' }}</p>
                                </div>
                                <div class="col-md-6">
                                    @if($order->address)
                                        <p><strong>Address:</strong><br>
                                            {{ $order->address->address_line_1 }}<br>
                                            @if($order->address->address_line_2)
                                                {{ $order->address->address_line_2 }}<br>
                                            @endif
                                            {{ $order->address->city }}, {{ $order->address->state ?? 'N/A' }}<br>
                                            {{ $order->address->zip_code }}
                                        </p>
                                    @else
                                        <p class="text-muted">No address on file</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Order Items -->
                    <div class="card shadow-sm mb-3">
                        <div class="card-header bg-success text-white">
                            <h3 class="card-title mb-0"><i class="fas fa-list"></i> Order Items</h3>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Product</th>
                                        <th width="10%" class="text-center">Qty</th>
                                        <th width="15%" class="text-center">Price</th>
                                        <th width="15%" class="text-center">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->items as $item)
                                        <tr>
                                            <td>
                                                @if($item->product)
                                                    <strong>{{ $item->product->name }}</strong><br>
                                                    <small class="text-muted">SKU: {{ $item->product->id }}</small>
                                                @else
                                                    <strong>Product (Deleted)</strong>
                                                @endif
                                            </td>
                                            <td class="text-center">{{ $item->quantity }}</td>
                                            <td class="text-center">৳{{ number_format($item->price, 2) }}</td>
                                            <td class="text-center"><strong>৳{{ number_format($item->total, 2) }}</strong></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Order Notes -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-warning text-dark">
                            <h3 class="card-title mb-0"><i class="fas fa-sticky-note"></i> Order Notes</h3>
                        </div>
                        <div class="card-body">
                            <form id="notesForm">
                                <textarea class="form-control" id="notes" name="notes" rows="3">{{ $order->notes }}</textarea>
                                <button type="submit" class="btn btn-primary btn-sm mt-2">
                                    <i class="fas fa-save"></i> Save Notes
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="col-lg-4">
                    <!-- Order Status -->
                    <div class="card shadow-sm mb-3">
                        <div class="card-header bg-primary text-white">
                            <h3 class="card-title mb-0"><i class="fas fa-info-circle"></i> Order Status</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label"><strong>Current Status:</strong></label>
                                <select class="form-select" id="statusSelect">
                                    @foreach($statuses as $status)
                                        <option value="{{ $status }}" {{ $order->status == $status ? 'selected' : '' }}>
                                            {{ ucfirst($status) }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-primary btn-sm w-100 mt-2" id="updateStatusBtn">
                                    <i class="fas fa-check"></i> Update Status
                                </button>
                            </div>

                            @if($order->shipped_at)
                                <p class="text-muted mb-0">
                                    <i class="fas fa-truck"></i> <strong>Shipped:</strong><br>
                                    {{ $order->shipped_at->format('M d, Y h:i A') }}
                                </p>
                            @endif

                            @if($order->delivered_at)
                                <p class="text-success mb-0">
                                    <i class="fas fa-check-circle"></i> <strong>Delivered:</strong><br>
                                    {{ $order->delivered_at->format('M d, Y h:i A') }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <!-- Payment Status -->
                    <div class="card shadow-sm mb-3">
                        <div class="card-header bg-info text-white">
                            <h3 class="card-title mb-0"><i class="fas fa-credit-card"></i> Payment Status</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-2">
                                <label class="form-label"><strong>Status:</strong></label>
                                <select class="form-select" id="paymentStatusSelect">
                                    @foreach($paymentStatuses as $pStatus)
                                        <option value="{{ $pStatus }}" {{ $order->payment_status == $pStatus ? 'selected' : '' }}>
                                            {{ ucfirst($pStatus) }}
                                        </option>
                                    @endforeach
                                </select>
                                <button type="button" class="btn btn-info btn-sm w-100 mt-2" id="updatePaymentBtn">
                                    <i class="fas fa-check"></i> Update Payment
                                </button>
                            </div>

                            <p class="mb-1">
                                <strong>Method:</strong> {{ strtoupper($order->payment_method) }}
                            </p>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-success text-white">
                            <h3 class="card-title mb-0"><i class="fas fa-calculator"></i> Order Summary</h3>
                        </div>
                        <div class="card-body">
                            <div class="row mb-2">
                                <div class="col-6"><strong>Subtotal:</strong></div>
                                <div class="col-6 text-end">৳{{ number_format($order->subtotal, 2) }}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-6"><strong>Shipping:</strong></div>
                                <div class="col-6 text-end">৳{{ number_format($order->shipping_cost, 2) }}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-6"><strong>Tax:</strong></div>
                                <div class="col-6 text-end">৳{{ number_format($order->tax, 2) }}</div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-6"><strong>Discount:</strong></div>
                                <div class="col-6 text-end">-৳{{ number_format($order->discount, 2) }}</div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-6"><strong>Total Amount:</strong></div>
                                <div class="col-6 text-end"><strong>৳{{ number_format($order->total, 2) }}</strong></div>
                            </div>
                        </div>
                    </div>
                </div>
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

    // Update Status
    $('#updateStatusBtn').on('click', function() {
        const status = $('#statusSelect').val();

        $.ajax({
            url: '{{ route("admin.orders.update-status", $order->id) }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                status: status
            },
            success: function(response) {
                if (response.success) {
                    showToast('success', response.message);
                    setTimeout(() => location.reload(), 1500);
                }
            },
            error: function(xhr) {
                showToast('error', 'Failed to update status');
            }
        });
    });

    // Update Payment Status
    $('#updatePaymentBtn').on('click', function() {
        const paymentStatus = $('#paymentStatusSelect').val();

        $.ajax({
            url: '{{ route("admin.orders.update-payment-status", $order->id) }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                payment_status: paymentStatus
            },
            success: function(response) {
                if (response.success) {
                    showToast('success', response.message);
                    setTimeout(() => location.reload(), 1500);
                }
            },
            error: function(xhr) {
                showToast('error', 'Failed to update payment status');
            }
        });
    });

    // Save Notes
    $('#notesForm').on('submit', function(e) {
        e.preventDefault();

        $.ajax({
            url: '{{ route("admin.orders.update-notes", $order->id) }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                notes: $('#notes').val()
            },
            success: function(response) {
                if (response.success) {
                    showToast('success', response.message);
                }
            },
            error: function(xhr) {
                showToast('error', 'Failed to save notes');
            }
        });
    });

    // Toast notification
    function showToast(type, message) {
        const bgColor = type === 'success' ? 'bg-success' : 'bg-danger';
        const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';

        const toast = $(`
            <div class="position-fixed top-0 end-0 p-3" style="z-index: 11000">
                <div class="toast show ${bgColor} text-white" role="alert">
                    <div class="toast-body">
                        <i class="fas ${icon} me-2"></i>${message}
                    </div>
                </div>
            </div>
        `);

        $('body').append(toast);

        setTimeout(() => {
            toast.fadeOut(300, function() {
                $(this).remove();
            });
        }, 3000);
    }
});
</script>
@endpush

@endsection
