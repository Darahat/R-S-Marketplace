<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $order->order_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f5f5f5;
            padding: 20px;
        }
        .invoice {
            background-color: white;
            padding: 40px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            max-width: 900px;
            margin: 0 auto;
        }
        .invoice-header {
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .invoice-title {
            font-size: 28px;
            font-weight: bold;
            color: #007bff;
        }
        .invoice-number {
            font-size: 14px;
            color: #666;
        }
        .section-title {
            font-weight: bold;
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 8px;
            margin-top: 20px;
            margin-bottom: 15px;
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: bold;
            border-top: 1px solid #ddd;
        }
        .summary-table {
            width: 100%;
            margin-top: 20px;
        }
        .summary-table td {
            padding: 8px;
        }
        .summary-table .label {
            text-align: right;
            font-weight: 600;
            width: 60%;
        }
        .summary-table .amount {
            text-align: right;
            width: 40%;
        }
        .summary-table .total-row {
            background-color: #007bff;
            color: white;
            font-weight: bold;
            font-size: 16px;
        }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            color: #666;
            font-size: 12px;
        }
        @media print {
            body {
                background-color: white;
                padding: 0;
            }
            .invoice {
                box-shadow: none;
            }
            .print-btn {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="invoice">
        <!-- Header -->
        <div class="invoice-header">
            <div class="row">
                <div class="col-md-6">
                    <div class="invoice-title">INVOICE</div>
                    <div class="invoice-number">{{ $order->order_number }}</div>
                </div>
                <div class="col-md-6 text-end">
                    <p class="mb-1"><strong>Invoice Date:</strong> {{ $order->created_at->format('F d, Y') }}</p>
                    <p class="mb-1"><strong>Order Status:</strong> <span class="badge bg-info">{{ ucfirst($order->order_status) }}</span></p>
                    <p class="mb-0"><strong>Payment Status:</strong> <span class="badge bg-success">{{ ucfirst($order->payment_status) }}</span></p>
                </div>
            </div>
        </div>

        <!-- Customer Information -->
        <div class="row">
            <div class="col-md-6">
                <div class="section-title">CUSTOMER INFORMATION</div>
                <p class="mb-1"><strong>{{ $order->address->full_name ?? $order->user->name }}</strong></p>
                <p class="mb-1">{{ $order->address->email ?? $order->user->email }}</p>
                <p class="mb-0">{{ $order->address->phone ?? $order->user->phone ?? 'N/A' }}</p>
            </div>
            <div class="col-md-6">
                <div class="section-title">SHIPPING ADDRESS</div>
                @if($order->address)
                    <p class="mb-1">{{ $order->address->street_address }}</p>
                    <p class="mb-1">{{ $order->address->postal_code }}</p>
                    <p class="mb-0">
                        @if($order->address->union) {{ $order->address->union->name }}, @endif
                        @if($order->address->upazila) {{ $order->address->upazila->name }}, @endif
                        @if($order->address->district) {{ $order->address->district->name }} @endif
                    </p>
                @else
                    <p class="text-muted">No address on file</p>
                @endif
            </div>
        </div>

        <!-- Order Items -->
        <div class="section-title">ORDER DETAILS</div>
        <table class="table table-sm table-bordered">
            <thead>
                <tr>
                    <th width="50%">Product</th>
                    <th width="15%" class="text-center">Quantity</th>
                    <th width="17%" class="text-center">Unit Price</th>
                    <th width="18%" class="text-center">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td>
                            @if($item->product)
                                {{ $item->product->name }}
                            @else
                                <em>Product (Deleted)</em>
                            @endif
                        </td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-center">৳{{ number_format($item->price, 2) }}</td>
                        <td class="text-center">৳{{ number_format($item->total, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Summary -->
        @php
            $calcSubtotal = $order->subtotal ?? $order->items->sum('total');
            $calcShipping = $order->shipping_cost ?? 0;
            $calcTax = $order->tax ?? 0;
            $calcDiscount = $order->discount ?? 0;
            $calcTotal = $order->total_amount ?? ($calcSubtotal + $calcShipping + $calcTax - $calcDiscount);
        @endphp
        <table class="summary-table">
            <tr>
                <td class="label">Subtotal:</td>
                <td class="amount">৳{{ number_format($calcSubtotal, 2) }}</td>
            </tr>
            <tr>
                <td class="label">Shipping Cost:</td>
                <td class="amount">৳{{ number_format($calcShipping, 2) }}</td>
            </tr>
            <tr>
                <td class="label">Tax:</td>
                <td class="amount">৳{{ number_format($calcTax, 2) }}</td>
            </tr>
            @if($calcDiscount > 0)
            <tr>
                <td class="label">Discount:</td>
                <td class="amount">-৳{{ number_format($calcDiscount, 2) }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td class="label">TOTAL AMOUNT:</td>
                <td class="amount">৳{{ number_format($calcTotal, 2) }}</td>
            </tr>
        </table>

        <!-- Additional Info -->
        <div class="row mt-4">
            <div class="col-md-6">
                <div class="section-title">PAYMENT METHOD</div>
                <p>{{ strtoupper(str_replace('_', ' ', $order->payment_method)) }}</p>
            </div>
            <div class="col-md-6">
                <div class="section-title">ORDER TIMELINE</div>
                @if($order->shipped_at)
                    <p class="mb-1"><strong>Shipped:</strong> {{ $order->shipped_at->format('F d, Y h:i A') }}</p>
                @endif
                @if($order->delivered_at)
                    <p class="mb-0"><strong>Delivered:</strong> {{ $order->delivered_at->format('F d, Y h:i A') }}</p>
                @endif
            </div>
        </div>

        @if($order->notes)
        <div class="section-title">NOTES</div>
        <p>{{ $order->notes }}</p>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p class="mb-0">Thank you for your purchase! For any questions, please contact our support team.</p>
            <p>{{ config('app.name') }} - {{ now()->format('Y') }} © All rights reserved.</p>
        </div>
    </div>

    <div class="text-center mt-3">
        <button class="btn btn-primary print-btn" onclick="window.print()">
            <i class="fas fa-print"></i> Print Invoice
        </button>
        <button class="btn btn-secondary print-btn" onclick="window.close()">
            <i class="fas fa-times"></i> Close
        </button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
