@extends('backend_panel_view.layouts.admin')
@section('content')

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-chart-line"></i> {{ $page_header }}</h1>
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

            <!-- Overview Cards -->
            <div class="row">
                <!-- Total Revenue -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>৳{{ number_format($analytics['total_revenue'], 2) }}</h3>
                            <p>Total Revenue</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="small-box-footer">
                            This Month: ৳{{ number_format($analytics['month_revenue'], 2) }}
                        </div>
                    </div>
                </div>

                <!-- Total Profit -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>৳{{ number_format($analytics['total_profit'], 2) }}</h3>
                            <p>Total Profit</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div class="small-box-footer">
                            This Month: ৳{{ number_format($analytics['month_profit'], 2) }}
                        </div>
                    </div>
                </div>

                <!-- Total Orders -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ $analytics['total_orders'] }}</h3>
                            <p>Total Orders</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="small-box-footer">
                            Today: {{ $analytics['today_orders'] }} orders
                        </div>
                    </div>
                </div>

                <!-- Total Products -->
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>{{ $analytics['total_products'] }}</h3>
                            <p>Total Products</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="small-box-footer">
                            Low Stock: {{ $analytics['low_stock_products'] }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Status Cards -->
            <div class="row">
                <div class="col-md-2 col-sm-4 col-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning"><i class="fas fa-clock"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Pending</span>
                            <span class="info-box-number">{{ $analytics['pending_orders'] }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-2 col-sm-4 col-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="fas fa-cog"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Processing</span>
                            <span class="info-box-number">{{ $analytics['processing_orders'] }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-2 col-sm-4 col-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-primary"><i class="fas fa-shipping-fast"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Shipped</span>
                            <span class="info-box-number">{{ $analytics['shipped_orders'] }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-2 col-sm-4 col-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-success"><i class="fas fa-check-circle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Delivered</span>
                            <span class="info-box-number">{{ $analytics['delivered_orders'] }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-2 col-sm-4 col-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-danger"><i class="fas fa-times-circle"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Cancelled</span>
                            <span class="info-box-number">{{ $analytics['cancelled_orders'] }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-md-2 col-sm-4 col-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-secondary"><i class="fas fa-money-check-alt"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Pending Pay</span>
                            <span class="info-box-number">{{ $analytics['pending_payments'] }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="row">
                <!-- Sales Chart -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-chart-bar"></i> Monthly Sales & Orders (Last 12 Months)</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="salesChart" height="80"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Profit Chart -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-chart-line"></i> Monthly Profit Trend</h3>
                        </div>
                        <div class="card-body">
                            <canvas id="profitChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Products & Recent Orders -->
            <div class="row">
                <!-- Top Selling Products -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-fire"></i> Top Selling Products (Last 30 Days)</h3>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th class="text-center">Sold</th>
                                        <th class="text-right">Revenue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($analytics['top_products'] as $product)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ asset($product->image) }}" alt="{{ $product->name }}"
                                                     class="img-thumbnail mr-2" style="width: 40px; height: 40px; object-fit: cover;">
                                                <span>{{ Str::limit($product->name, 30) }}</span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-info">{{ $product->total_sold }}</span>
                                        </td>
                                        <td class="text-right">৳{{ number_format($product->total_revenue, 2) }}</td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No sales data available</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Recent Orders -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-list"></i> Recent Orders</h3>
                            <div class="card-tools">
                                <a href="{{ route('admin.orders') }}" class="btn btn-sm btn-primary">View All</a>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Order#</th>
                                        <th>Customer</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($analytics['recent_orders'] as $order)
                                    <tr>
                                        <td><small>{{ $order->order_number }}</small></td>
                                        <td><small>{{ $order->user->name }}</small></td>
                                        <td><small>৳{{ number_format($order->total_amount, 2) }}</small></td>
                                        <td>
                                            <span class="badge badge-{{
                                                $order->order_status == 'delivered' ? 'success' :
                                                ($order->order_status == 'cancelled' ? 'danger' : 'warning')
                                            }}">
                                                {{ ucfirst($order->order_status) }}
                                            </span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No recent orders</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

@push('scripts')
<script>
// Sales Chart
const salesCtx = document.getElementById('salesChart').getContext('2d');
new Chart(salesCtx, {
    type: 'bar',
    data: {
        labels: {!! json_encode(array_column($analytics['monthly_sales'], 'month')) !!},
        datasets: [{
            label: 'Revenue (৳)',
            data: {!! json_encode(array_column($analytics['monthly_sales'], 'sales')) !!},
            backgroundColor: 'rgba(54, 162, 235, 0.6)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1,
            yAxisID: 'y'
        }, {
            label: 'Orders',
            data: {!! json_encode(array_column($analytics['monthly_sales'], 'orders')) !!},
            backgroundColor: 'rgba(255, 159, 64, 0.6)',
            borderColor: 'rgba(255, 159, 64, 1)',
            borderWidth: 1,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                position: 'left',
                title: {
                    display: true,
                    text: 'Revenue (৳)'
                }
            },
            y1: {
                beginAtZero: true,
                position: 'right',
                grid: {
                    drawOnChartArea: false
                },
                title: {
                    display: true,
                    text: 'Orders'
                }
            }
        }
    }
});

// Profit Chart
const profitCtx = document.getElementById('profitChart').getContext('2d');
new Chart(profitCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode(array_column($analytics['monthly_profit'], 'month')) !!},
        datasets: [{
            label: 'Profit (৳)',
            data: {!! json_encode(array_column($analytics['monthly_profit'], 'profit')) !!},
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            borderColor: 'rgba(75, 192, 192, 1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Profit (৳)'
                }
            }
        }
    }
});
</script>
@endpush

@endsection
