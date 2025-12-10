@extends('backend_panel_view.layouts.admin')
@section('content')

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-credit-card"></i> {{ $page_header }}</h1>
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
                <div class="col-md-2-4">
                    <div class="card bg-primary text-white shadow-sm">
                        <div class="card-body p-3">
                            <h6 class="card-title mb-0">Total Payments</h6>
                            <h3 class="mb-0" id="totalPayments">-</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-2-4">
                    <div class="card bg-success text-white shadow-sm">
                        <div class="card-body p-3">
                            <h6 class="card-title mb-0">Completed</h6>
                            <h3 class="mb-0" id="completedPayments">-</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-2-4">
                    <div class="card bg-warning text-white shadow-sm">
                        <div class="card-body p-3">
                            <h6 class="card-title mb-0">Pending</h6>
                            <h3 class="mb-0" id="pendingPayments">-</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-2-4">
                    <div class="card bg-danger text-white shadow-sm">
                        <div class="card-body p-3">
                            <h6 class="card-title mb-0">Failed</h6>
                            <h3 class="mb-0" id="failedPayments">-</h3>
                        </div>
                    </div>
                </div>
                <div class="col-md-2-4">
                    <div class="card bg-info text-white shadow-sm">
                        <div class="card-body p-3">
                            <h6 class="card-title mb-0">Total Revenue</h6>
                            <h3 class="mb-0" id="totalRevenue">-</h3>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters Card -->
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.payments') }}" class="row g-2">
                        <div class="col-md-2">
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="Transaction ID" value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <select name="payment_status" class="form-select form-select-sm">
                                <option value="">All Status</option>
                                @foreach($paymentStatuses as $status)
                                    <option value="{{ $status }}" {{ request('payment_status') == $status ? 'selected' : '' }}>
                                        {{ ucfirst($status) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="payment_method" class="form-select form-select-sm">
                                <option value="">All Methods</option>
                                @foreach($paymentMethods as $method)
                                    <option value="{{ $method }}" {{ request('payment_method') == $method ? 'selected' : '' }}>
                                        {{ strtoupper($method) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-2">
                            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary btn-sm w-100"><i class="fas fa-search"></i> Filter</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Payments Table -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0"><i class="fas fa-list"></i> All Payments ({{ $payments->total() }})</h3>
                </div>

                <div class="card-body table-responsive p-0">
                    <table class="table table-hover table-striped align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th width="12%">Transaction ID</th>
                                <th width="12%">Order #</th>
                                <th width="12%">Customer</th>
                                <th width="10%" class="text-center">Amount</th>
                                <th width="12%" class="text-center">Status</th>
                                <th width="10%" class="text-center">Method</th>
                                <th width="14%">Date</th>
                                <th width="18%" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payments as $payment)
                                <tr>
                                    <td>
                                        <code>{{ substr($payment->transaction_id, 0, 15) }}...</code>
                                    </td>
                                    <td>
                                        @if($payment->order)
                                            <strong>{{ $payment->order->order_number }}</strong>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $payment->user->name }}</strong><br>
                                        <small class="text-muted">{{ $payment->user->email }}</small>
                                    </td>
                                    <td class="text-center">
                                        <strong>৳{{ number_format($payment->amount, 2) }}</strong>
                                        @if($payment->fee > 0)
                                            <br><small class="text-muted">+৳{{ number_format($payment->fee, 2) }} fee</small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge
                                            {{ $payment->payment_status == 'pending' ? 'bg-secondary' : '' }}
                                            {{ $payment->payment_status == 'processing' ? 'bg-info' : '' }}
                                            {{ $payment->payment_status == 'completed' ? 'bg-success' : '' }}
                                            {{ $payment->payment_status == 'failed' ? 'bg-danger' : '' }}
                                            {{ $payment->payment_status == 'refunded' ? 'bg-dark' : '' }}
                                        ">
                                            {{ ucfirst($payment->payment_status) }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary">{{ strtoupper($payment->payment_method) }}</span>
                                    </td>
                                    <td>
                                        <small>{{ $payment->created_at->format('M d, Y') }}</small><br>
                                        <small class="text-muted">{{ $payment->created_at->format('h:i A') }}</small>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('admin.payments.show', $payment->id) }}"
                                               class="btn btn-info"
                                               data-bs-toggle="tooltip"
                                               title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($payment->payment_status == 'pending')
                                                <button class="btn btn-success process-btn"
                                                        data-id="{{ $payment->id }}"
                                                        data-bs-toggle="tooltip"
                                                        title="Process Payment">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            @endif
                                            @if($payment->payment_status == 'completed')
                                                <button class="btn btn-warning refund-btn"
                                                        data-id="{{ $payment->id }}"
                                                        data-bs-toggle="tooltip"
                                                        title="Refund">
                                                    <i class="fas fa-undo"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <p>No payments found.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($payments->hasPages())
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Showing {{ $payments->firstItem() }} to {{ $payments->lastItem() }} of {{ $payments->total() }} payments
                        </div>
                        <nav>
                            {{ $payments->links('pagination::bootstrap-5') }}
                        </nav>
                    </div>
                </div>
                @endif
            </div>

        </div>
    </section>
</div>

<!-- Refund Modal -->
<div class="modal fade" id="refundModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title"><i class="fas fa-undo"></i> Refund Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label"><strong>Refund Amount (৳)</strong></label>
                    <input type="number" id="refundAmount" class="form-control" min="0" step="0.01" placeholder="0.00">
                    <small class="form-text text-muted">Max available: <strong id="maxRefund">0.00</strong></small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Reason/Notes</label>
                    <textarea id="refundNotes" class="form-control" rows="3" placeholder="Enter refund reason"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-warning" id="confirmRefund">Process Refund</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {

    // Load statistics
    loadStatistics();

    function loadStatistics() {
        $.ajax({
            url: '{{ route("admin.payments.statistics") }}',
            type: 'GET',
            success: function(data) {
                $('#totalPayments').text(data.total_payments);
                $('#completedPayments').text(data.completed_payments);
                $('#pendingPayments').text(data.pending_payments);
                $('#failedPayments').text(data.failed_payments);
                $('#totalRevenue').text('৳' + Number(data.completed_amount).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
            }
        });
    }

    // Process Payment
    let currentPaymentId = null;

    $('.process-btn').on('click', function() {
        currentPaymentId = $(this).data('id');

        if (confirm('Are you sure you want to process this payment?')) {
            $.ajax({
                url: '/admin/payments/' + currentPaymentId + '/process',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        showToast('success', response.message);
                        setTimeout(() => location.reload(), 1500);
                    }
                },
                error: function() {
                    showToast('error', 'Failed to process payment');
                }
            });
        }
    });

    // Refund Payment
    $('.refund-btn').on('click', function() {
        currentPaymentId = $(this).data('id');
        const $row = $(this).closest('tr');
        const amount = parseFloat($row.find('td:eq(3)').text().replace('৳', '').trim());

        $('#maxRefund').text(amount.toFixed(2));
        $('#refundAmount').val(amount.toFixed(2));
        $('#refundNotes').val('');
        $('#refundModal').modal('show');
    });

    $('#confirmRefund').on('click', function() {
        const refundAmount = parseFloat($('#refundAmount').val());
        const refundNotes = $('#refundNotes').val();

        if (!refundAmount || refundAmount <= 0) {
            alert('Please enter a valid refund amount');
            return;
        }

        $.ajax({
            url: '/admin/payments/' + currentPaymentId + '/refund',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                refund_amount: refundAmount,
                notes: refundNotes
            },
            success: function(response) {
                if (response.success) {
                    $('#refundModal').modal('hide');
                    showToast('success', response.message);
                    setTimeout(() => location.reload(), 1500);
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Failed to process refund';
                showToast('error', message);
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
