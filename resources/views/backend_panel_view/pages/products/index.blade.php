@extends('backend_panel_view.layouts.admin')
@section('content')

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-box"></i> {{ $page_header }}</h1>
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

            <!-- Filters Card -->
            <div class="card shadow-sm mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.products.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <input type="text" name="search" class="form-control" placeholder="Search products..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <select name="category" class="form-select">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="brand" class="form-select">
                                <option value="">All Brands</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}" {{ request('brand') == $brand->id ? 'selected' : '' }}>
                                        {{ $brand->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="featured" {{ request('status') == 'featured' ? 'selected' : '' }}>Featured</option>
                                <option value="best_selling" {{ request('status') == 'best_selling' ? 'selected' : '' }}>Best Selling</option>
                                <option value="latest" {{ request('status') == 'latest' ? 'selected' : '' }}>Latest</option>
                                <option value="flash_sale" {{ request('status') == 'flash_sale' ? 'selected' : '' }}>Flash Sale</option>
                                <option value="low_stock" {{ request('status') == 'low_stock' ? 'selected' : '' }}>Low Stock</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Filter</button>
                            <a href="{{ route('admin.products.index') }}" class="btn btn-secondary"><i class="fas fa-redo"></i> Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Products Card -->
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0"><i class="fas fa-list"></i> All Products ({{ $products->total() }})</h3>
                    <div>
                        <button class="btn btn-danger btn-sm me-2" id="bulkDeleteBtn" style="display: none;">
                            <i class="fas fa-trash"></i> Delete Selected
                        </button>
                        <a href="{{ route('admin.products.create') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-plus"></i> Add New Product
                        </a>
                    </div>
                </div>

                <div class="card-body table-responsive p-0">
                    <table class="table table-hover table-striped text-nowrap align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th width="3%">
                                    <input type="checkbox" id="selectAll">
                                </th>
                                <th width="7%">Image</th>
                                <th width="20%">Name</th>
                                <th width="10%">Category</th>
                                <th width="10%">Brand</th>
                                <th width="8%">Price</th>
                                <th width="7%" class="text-center">Stock</th>
                                <th width="7%" class="text-center">Sold</th>
                                <th width="8%" class="text-center">Featured</th>
                                <th width="20%" class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($products as $product)
                                <tr>
                                    <td>
                                        <input type="checkbox" class="product-checkbox" value="{{ $product->id }}">
                                    </td>
                                    <td>
                                        <img src="{{ $product->image ? (filter_var($product->image, FILTER_VALIDATE_URL) ? $product->image : asset('storage/' . $product->image)) : 'https://via.placeholder.com/50' }}"
                                             alt="{{ $product->name }}"
                                             class="img-thumbnail"
                                             style="width: 50px; height: 50px; object-fit: cover;">
                                    </td>
                                    <td>
                                        <strong>{{ $product->name }}</strong><br>
                                        <small class="text-muted">{{ Str::limit($product->description, 40) }}</small>
                                    </td>
                                    <td>
                                        @if($product->category)
                                            <span class="badge bg-secondary">{{ $product->category->name }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($product->brand)
                                            <span class="badge bg-info">{{ $product->brand->name }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>৳{{ number_format($product->price, 2) }}</strong>
                                        @if($product->discount_price > 0)
                                            <br><small class="text-danger">৳{{ number_format($product->discount_price, 2) }}</small>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($product->stock < 10)
                                            <span class="badge bg-danger">{{ $product->stock }}</span>
                                        @elseif($product->stock < 50)
                                            <span class="badge bg-warning">{{ $product->stock }}</span>
                                        @else
                                            <span class="badge bg-success">{{ $product->stock }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-primary">{{ $product->sold_count }}</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-inline-block">
                                            <input class="form-check-input featured-toggle" type="checkbox"
                                                   data-id="{{ $product->id }}" {{ $product->featured ? 'checked' : '' }}>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('admin.products.show', $product->id) }}"
                                               class="btn btn-info"
                                               data-bs-toggle="tooltip"
                                               title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.products.edit', $product->id) }}"
                                               class="btn btn-warning"
                                               data-bs-toggle="tooltip"
                                               title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button class="btn btn-danger delete-btn"
                                                    data-id="{{ $product->id }}"
                                                    data-name="{{ $product->name }}"
                                                    data-bs-toggle="tooltip"
                                                    title="Delete">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-3x mb-3"></i>
                                        <p>No products found. Click "Add New Product" to create one.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($products->hasPages())
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }} products
                        </div>
                        <nav>
                            {{ $products->links('pagination::bootstrap-5') }}
                        </nav>
                    </div>
                </div>
                @endif
            </div>

        </div>
    </section>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle"></i> Confirm Delete</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete "<strong id="productName"></strong>"?</p>
                <p class="text-danger"><small><i class="fas fa-info-circle"></i> This action cannot be undone!</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Yes, Delete</button>
            </div>
        </div>
    </div>
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

    // Select All Checkbox
    $('#selectAll').on('change', function() {
        $('.product-checkbox').prop('checked', this.checked);
        toggleBulkDelete();
    });

    $('.product-checkbox').on('change', function() {
        toggleBulkDelete();
    });

    function toggleBulkDelete() {
        const checked = $('.product-checkbox:checked').length;
        if (checked > 0) {
            $('#bulkDeleteBtn').show();
        } else {
            $('#bulkDeleteBtn').hide();
        }
    }

    // Bulk Delete
    $('#bulkDeleteBtn').on('click', function() {
        const ids = [];
        $('.product-checkbox:checked').each(function() {
            ids.push($(this).val());
        });

        if (confirm('Are you sure you want to delete ' + ids.length + ' products?')) {
            $.ajax({
                url: '{{ route("admin.products.bulk-delete") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    ids: ids
                },
                success: function(response) {
                    if (response.success) {
                        showToast('success', response.message);
                        setTimeout(() => location.reload(), 1500);
                    }
                },
                error: function() {
                    showToast('error', 'Failed to delete products');
                }
            });
        }
    });

    // Featured Toggle
    $('.featured-toggle').on('change', function() {
        const productId = $(this).data('id');
        const isChecked = $(this).is(':checked');
        const $toggle = $(this);

        $.ajax({
            url: '/admin/products/' + productId + '/toggle-featured',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    showToast('success', response.message);
                }
            },
            error: function() {
                showToast('error', 'Failed to update featured status');
                $toggle.prop('checked', !isChecked);
            }
        });
    });

    // Delete Product
    let deleteProductId = null;

    $('.delete-btn').on('click', function() {
        deleteProductId = $(this).data('id');
        const productName = $(this).data('name');

        $('#productName').text(productName);
        $('#deleteModal').modal('show');
    });

    $('#confirmDelete').on('click', function() {
        if (deleteProductId) {
            $.ajax({
                url: '/admin/products/' + deleteProductId,
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    if (response.success) {
                        $('#deleteModal').modal('hide');
                        showToast('success', response.message);
                        setTimeout(() => location.reload(), 1500);
                    }
                },
                error: function() {
                    showToast('error', 'Failed to delete product');
                }
            });
        }
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
