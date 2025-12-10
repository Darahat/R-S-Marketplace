@extends('backend_panel_view.layouts.admin')
@section('content')

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-receipt"></i> Payment Details</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.payments') }}">Payments</a></li>
                        <li class="breadcrumb-item active">Details</li>
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

            <div class="row">
                <!-- Payment Info Card -->
                <div class="col-md-8">
                    <div class="card shadow-sm">
                        <div class="card-header bg-primary text-white">
                            <div class="d-flex justify-content-between align-items-center">
                                <h3 class="card-title mb-0">
                                    <i class="fas fa-credit-card"></i> Payment Information
                                </h3>
                                <span class="badge
                                    {{ $payment->payment_status == 'pending' ? 'bg-secondary' : '' }}
                                    {{ $payment->payment_status == 'processing' ? 'bg-info' : '' }}
                                    {{ $payment->payment_status == 'completed' ? 'bg-success' : '' }}
                                    {{ $payment->payment_status == 'failed' ? 'bg-danger' : '' }}
                                    {{ $payment->payment_status == 'refunded' ? 'bg-dark' : '' }}
                                ">
                                    {{ ucfirst($payment->payment_status) }}
                                </span>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-1"><strong>Transaction ID</strong></h6>
                                    <code class="fs-6">{{ $payment->transaction_id }}</code>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-1"><strong>Payment Method</strong></h6>
                                    <span class="badge bg-secondary fs-6">{{ strtoupper($payment->payment_method) }}</span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-1"><strong>Amount</strong></h6>
                                    <h4 class="mb-0 text-success">৳{{ number_format($payment->amount, 2) }}</h4>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-1"><strong>Fee</strong></h6>
                                    <p class="mb-0">৳{{ number_format($payment->fee, 2) }}</p>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-1"><strong>Total Amount</strong></h6>
                                    <h5 class="mb-0">৳{{ number_format($payment->total_amount, 2) }}</h5>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="text-muted mb-1"><strong>Created</strong></h6>
                                    <p class="mb-0">{{ $payment->created_at->format('M d, Y h:i A') }}</p>
                                </div>
                            </div>

                            <hr>

                            @if($payment->paid_at)
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <h6 class="text-muted mb-1"><strong>Paid At</strong></h6>
                                        <p class="mb-0">{{ $payment->paid_at->format('M d, Y h:i A') }}</p>
                                    </div>
                                </div>
                            @endif

                            @if($payment->refunded_at)
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <h6 class="text-muted mb-1"><strong>Refunded At</strong></h6>
                                        <p class="mb-0">{{ $payment->refunded_at->format('M d, Y h:i A') }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Order Info Card -->
                    @if($payment->order)
                        <div class="card shadow-sm mt-3">
                            <div class="card-header bg-info text-white">
                                <h3 class="card-title mb-0"><i class="fas fa-shopping-cart"></i> Related Order</h3>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <h6 class="text-muted mb-1"><strong>Order Number</strong></h6>
                                        <a href="{{ route('admin.orders.show', $payment->order->id) }}" class="fs-6">
                                            {{ $payment->order->order_number }}
                                        </a>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-muted mb-1"><strong>Order Status</strong></h6>
                                        <span class="badge
                                            {{ $payment->order->order_status == 'pending' ? 'bg-secondary' : '' }}
                                            {{ $payment->order->order_status == 'processing' ? 'bg-info' : '' }}
                                            {{ $payment->order->order_status == 'shipped' ? 'bg-primary' : '' }}
                                            {{ $payment->order->order_status == 'completed' ? 'bg-success' : '' }}
                                            {{ $payment->order->order_status == 'cancelled' ? 'bg-danger' : '' }}
                                        ">
                                            {{ ucfirst($payment->order->order_status) }}
                                        </span>
                                    </div>
                                </div>

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <h6 class="text-muted mb-1"><strong>Order Total</strong></h6>
                                        <h5 class="mb-0">৳{{ number_format($payment->order->total_amount, 2) }}</h5>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-muted mb-1"><strong>Items</strong></h6>
                                        <p class="mb-0">{{ $payment->order->items->count() }} items</p>
                                    </div>
                                </div>

                                <a href="{{ route('admin.orders.show', $payment->order->id) }}" class="btn btn-info btn-sm">
                                    <i class="fas fa-eye"></i> View Full Order
                                </a>
                            </div>
                        </div>
                    @endif

                    <!-- Notes Card -->
                    <div class="card shadow-sm mt-3">
                        <div class="card-header bg-secondary text-white">
                            <h3 class="card-title mb-0"><i class="fas fa-sticky-note"></i> Payment Notes</h3>
                        </div>
                        <div class="card-body">
                            <div id="notesContainer">
                                @if($payment->notes)
                                    <p>{{ $payment->notes }}</p>
                                @else
                                    <p class="text-muted">No notes added.</p>
                                @endif
                            </div>
                            <form id="notesForm" class="mt-3">
                                @csrf
                                <textarea id="notesInput" class="form-control" rows="3" placeholder="Add notes about this payment..." required>{{ $payment->notes }}</textarea>
                                <button type="button" class="btn btn-primary btn-sm mt-2" id="updateNotesBtn">
                                    <i class="fas fa-save"></i> Save Notes
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Action Sidebar -->
                <div class="col-md-4">
                    <!-- Customer Info -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-warning text-dark">
                            <h3 class="card-title mb-0"><i class="fas fa-user"></i> Customer Information</h3>
                        </div>
                        <div class="card-body">
                            <h5 class="mb-1">{{ $payment->user->name }}</h5>
                            <p class="text-muted mb-2">{{ $payment->user->email }}</p>
                            @if($payment->user->phone)
                                <p class="mb-2">
                                    <strong>Phone:</strong> {{ $payment->user->phone }}
                                </p>
                            @endif
                            <a href="#" class="btn btn-info btn-sm w-100">
                                <i class="fas fa-user-circle"></i> View Profile
                            </a>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="card shadow-sm mt-3">
                        <div class="card-header bg-danger text-white">
                            <h3 class="card-title mb-0"><i class="fas fa-cog"></i> Actions</h3>
                        </div>
                        <div class="card-body">
                            @if($payment->payment_status == 'pending')
                                <button type="button" class="btn btn-success w-100 mb-2" id="processPaymentBtn">
                                    <i class="fas fa-check"></i> Mark as Completed
                                </button>
                                <button type="button" class="btn btn-danger w-100" id="markFailedBtn">
                                    <i class="fas fa-times"></i> Mark as Failed
                                </button>
                            @elseif($payment->payment_status == 'completed')
                                <button type="button" class="btn btn-warning w-100 mb-2" id="refundPaymentBtn">
                                    <i class="fas fa-undo"></i> Refund Payment
                                </button>
                                <button type="button" class="btn btn-danger w-100" id="markFailedBtn">
                                    <i class="fas fa-times"></i> Mark as Failed
                                </button>
                            @endif

                            @if($payment->payment_status != 'refunded')
                                <a href="{{ route('admin.payments') }}" class="btn btn-secondary w-100 mt-2">
                                    <i class="fas fa-arrow-left"></i> Back to Payments
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Response Data -->
                    @if($payment->response_data)
                        <div class="card shadow-sm mt-3">
                            <div class="card-header bg-secondary text-white">
                                <h3 class="card-title mb-0"><i class="fas fa-code"></i> Response Data</h3>
                            </div>
                            <div class="card-body">
                                <pre class="mb-0" style="max-height: 300px; overflow-y: auto;"><code>{{ json_encode(json_decode($payment->response_data), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</code></pre>
                            </div>
                        </div>
                    @endif
                </div>
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
                    <input type="number" id="refundAmount" class="form-control" value="{{ $payment->amount }}" min="0" step="0.01" placeholder="0.00">
                    <small class="form-text text-muted">Max available: <strong>৳{{ number_format($payment->amount, 2) }}</strong></small>
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

<!-- Failed Reason Modal -->
<div class="modal fade" id="failedModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-times-circle"></i> Mark as Failed</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label"><strong>Failure Reason</strong></label>
                    <textarea id="failedNotes" class="form-control" rows="3" placeholder="Why did this payment fail?"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmFailed">Mark as Failed</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    const paymentId = {{ $payment->id }};

    // Process Payment
    $('#processPaymentBtn').on('click', function() {
        if (confirm('Are you sure you want to mark this payment as completed?')) {
            $.ajax({
                url: '/admin/payments/' + paymentId + '/process',
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
    $('#refundPaymentBtn').on('click', function() {
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
            url: '/admin/payments/' + paymentId + '/refund',
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

    // Mark as Failed
    $('#markFailedBtn').on('click', function() {
        $('#failedModal').modal('show');
    });

    $('#confirmFailed').on('click', function() {
        const notes = $('#failedNotes').val();

        $.ajax({
            url: '/admin/payments/' + paymentId + '/mark-failed',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                notes: notes
            },
            success: function(response) {
                if (response.success) {
                    $('#failedModal').modal('hide');
                    showToast('success', response.message);
                    setTimeout(() => location.reload(), 1500);
                }
            },
            error: function() {
                showToast('error', 'Failed to update payment status');
            }
        });
    });

    // Update Notes
    $('#updateNotesBtn').on('click', function() {
        const notes = $('#notesInput').val();

        $.ajax({
            url: '/admin/payments/' + paymentId + '/update-notes',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                notes: notes
            },
            success: function(response) {
                if (response.success) {
                    showToast('success', 'Notes updated successfully');
                    $('#notesContainer').html('<p>' + (notes || 'No notes added.') + '</p>');
                }
            },
            error: function() {
                showToast('error', 'Failed to update notes');
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
