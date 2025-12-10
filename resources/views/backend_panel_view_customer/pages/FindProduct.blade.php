@extends('backend_panel_view.layouts.admin')
@section('content')

<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $page_header }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">{{ $page_header }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">

        <!-- Search and Table -->
        <div class="card shadow-sm rounded-lg">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">Product List</h3>
                <input type="text" id="productSearch" class="form-control form-control-sm w-50" placeholder="Search products...">
            </div>

            <div class="card-body table-responsive p-0">
                <table id="productTable" class="table table-hover table-striped text-nowrap align-middle mb-0">
                    <thead class="bg-light text-dark">
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Purchase Price</th>
                            <th>Price</th>
                            <th>Discount</th>
                            <th>Stock</th>
                            <th>Sold</th>
                            <th colspan="3" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $index => $product)
                            <tr class="main-row" data-id="{{ $product->id }}">
                                <td>{{ $products->firstItem() + $index }}</td>
                                <td><strong>{{ $product->name }}</strong></td>
                                <td>৳{{ number_format($product->purchase_price, 2) }}</td>
                                <td>৳{{ number_format($product->price, 2) }}</td>
                                <td>
                                    @if($product->discount_price > 0)
                                        <span class="badge bg-warning text-dark">৳{{ number_format($product->discount_price, 2) }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($product->stock > 0)
                                        <span class="badge bg-success">{{ $product->stock }}</span>
                                    @else
                                        <span class="badge bg-danger">Out</span>
                                    @endif
                                </td>
                                <td>{{ $product->sold_count }}</td>
                                <td>
                                    <button class="btn btn-sm btn-info btn-toggle-details" data-id="{{ $product->id }}"><i class="fas fa-eye"></i></button>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></button>
                                </td>
                                <td>
                                    <form action="#" method="POST" onsubmit="return confirm('Delete this product?');">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger"><i class="fas fa-trash-alt"></i></button>
                                    </form>
                                </td>
                            </tr>
                            <!-- Details View -->
                            <tr class="details-row bg-light" id="details-{{ $product->id }}" style="display: none;">
                                <td colspan="11" class="p-0">
                                    <div class="p-4 bg-white border-top">
                                        <h5 class="mb-3 text-primary">Product Details</h5>

                                        <div class="row mb-2">
                                            <div class="col-md-4 mb-2">
                                                <strong>Name:</strong><br>{{ $product->name }}
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <strong>Slug:</strong><br>{{ $product->slug }}
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <strong>Category:</strong><br>{{ $product->category->name ?? 'N/A' }}
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <div class="col-md-4 mb-2">
                                                <strong>Purchase Price:</strong><br>৳{{ number_format($product->purchase_price, 2) }}
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <strong>Selling Price:</strong><br>৳{{ number_format($product->price, 2) }}
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <strong>Discount Price:</strong><br>
                                                @if($product->discount_price > 0)
                                                    ৳{{ number_format($product->discount_price, 2) }}
                                                @else
                                                    <span class="text-muted">No discount</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <div class="col-md-4 mb-2">
                                                <strong>Stock Quantity:</strong><br>{{ $product->stock }}
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <strong>Sold Count:</strong><br>{{ $product->sold_count }}
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <strong>Status:</strong><br>
                                                @if($product->stock > 0)
                                                    <span class="badge bg-success">In Stock</span>
                                                @else
                                                    <span class="badge bg-danger">Out of Stock</span>
                                                @endif
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <strong>Description:</strong>
                                            <div class="border rounded p-2 bg-light">
                                                {{ $product->description }}
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-2">
                                                <strong>Created At:</strong><br>
                                                {{ $product->created_at ? $product->created_at->format('d-m-Y') : 'N/A' }}
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <strong>Updated At:</strong><br>
                                                {{ $product->updated_at ? $product->updated_at->format('d-m-Y') : 'N/A' }}
                                            </div>

                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <!-- View Details End -->
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted py-4">No products found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-3">
            {{ $products->appends(request()->input())->links() }}
        </div>

    </section>
</div>

<!-- JS: Search + Details Toggle -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('productSearch');
        const table = document.getElementById('productTable');
        const rows = table.querySelectorAll('tbody tr.main-row');

        // Search
        searchInput.addEventListener('keyup', function () {
            const query = this.value.toLowerCase();
            rows.forEach(row => {
                const id = row.dataset.id;
                const detailsRow = document.getElementById('details-' + id);
                const match = row.innerText.toLowerCase().includes(query);
                row.style.display = match ? '' : 'none';
                if (detailsRow) detailsRow.style.display = 'none';
            });
        });

        // Toggle Details Row
        document.querySelectorAll('.btn-toggle-details').forEach(button => {
            button.addEventListener('click', function () {
                const id = this.dataset.id;
                const row = document.getElementById('details-' + id);
                row.style.display = row.style.display === 'none' ? '' : 'none';
            });
        });
    });
</script>

@endsection
