@extends('backend_panel_view.layouts.admin')
@section('content')

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


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
                                    <button class="btn btn-sm btn-warning btn-toggle-edit" data-id="{{ $product->id }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-danger delete-product-btn"
                                        data-product='@json($product)'
                                        data-url="{{ route('products.destroy', $product->id) }}">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </button>
                                </td>
                            </tr>


                            <!-- Details View -->
                            <tr class="details-row bg-light" id="details-{{ $product->id }}" style="display: none;">
                                <td colspan="11" class="p-0">
                                    <div class="p-4 bg-white border-top">
                                        <h5 class="mb-3 text-primary">Product Details</h5>

                                        <!-- Image & Basic Info -->
                                        <div class="row mb-4 align-items-start">
                                            <div class="col-md-3 text-center mb-3">
                                                <div class="border rounded p-2 bg-light">
                                                    @if($product->image)
                                                        <img src="{{ $product->image}}" alt="Product Image" class="img-fluid rounded shadow-sm" style="max-height: 120px; max-width: 100px; object-fit: cover;">
                                                    @else
                                                        <img src="{{ asset('images/default-product.jpeg') }}" alt="No Image" class="img-fluid rounded shadow-sm" style="max-height: 120px; max-width: 100px; object-fit: cover;">
                                                        <div class="text-muted small mt-2">No image available</div>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-md-9">
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
                                            </div>
                                        </div>

                                        <!-- Description -->
                                        <div class="mb-3">
                                            <strong>Description:</strong>
                                            <div class="border rounded p-2 bg-light">
                                                {{ $product->description }}
                                            </div>
                                        </div>

                                        <!-- Date Info -->
                                        <div class="row">
                                            <div class="col-md-6 mb-2">
                                                <strong>Created At:</strong><br>
                                                @if($product->created_at && is_object($product->created_at))
                                                    {{ $product->created_at->format('d-m-Y H:i') }}
                                                @elseif($product->created_at)
                                                    {{ date('d-m-Y H:i', strtotime($product->created_at)) }}
                                                @else
                                                    N/A
                                                @endif
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <strong>Updated At:</strong><br>
                                                @if($product->updated_at && is_object($product->updated_at))
                                                    {{ $product->updated_at->format('d-m-Y H:i') }}
                                                @elseif($product->updated_at)
                                                    {{ date('d-m-Y H:i', strtotime($product->updated_at)) }}
                                                @else
                                                    N/A
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <!-- View Details End -->

                            <!-- Edit Strat -->

                            <tr class="details-row" id="edit-details-{{ $product->id }}" style="display: none;">
                                <td colspan="11" class="p-0">
                                    <form action="#" method="POST" enctype="multipart/form-data" class="p-3 bg-light border">
                                        @csrf
                                        @method('PUT')
                                        <div class="row">
                                            <div class="col-md-4 mb-2">
                                                <label><strong>Name</strong></label>
                                                <input type="text" name="name" value="{{ $product->name }}" class="form-control">
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label><strong>Slug</strong></label>
                                                <input type="text" name="slug" value="{{ $product->slug }}" class="form-control">
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label><strong>Category</strong></label>
                                                <input type="text" class="form-control" value="{{ $product->category->name ?? 'N/A' }}" disabled>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 mb-2">
                                                <label><strong>Purchase Price</strong></label>
                                                <input type="number" step="0.01" name="purchase_price" value="{{ $product->purchase_price }}" class="form-control">
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label><strong>Selling Price</strong></label>
                                                <input type="number" step="0.01" name="price" value="{{ $product->price }}" class="form-control">
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label><strong>Discount Price</strong></label>
                                                <input type="number" step="0.01" name="discount_price" value="{{ $product->discount_price }}" class="form-control">
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-4 mb-2">
                                                <label><strong>Stock Quantity</strong></label>
                                                <input type="number" name="stock" value="{{ $product->stock }}" class="form-control">
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label><strong>Sold Count</strong></label>
                                                <input type="number" name="sold_count" value="{{ $product->sold_count }}" class="form-control">
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label><strong>Status</strong></label>
                                                <select name="status" class="form-control">
                                                    <option value="1" {{ $product->stock > 0 ? 'selected' : '' }}>In Stock</option>
                                                    <option value="0" {{ $product->stock <= 0 ? 'selected' : '' }}>Out of Stock</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="mb-2">
                                            <label><strong>Description</strong></label>
                                            <textarea name="description" class="form-control">{{ $product->description }}</textarea>
                                        </div>
                                        <div class="text-end">
                                            <button type="submit" class="btn btn-success btn-sm">Update Product</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>

                            <!-- Edit End -->


                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted py-4">No products found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                 <!-- Delete Confirmation Modal start-->
                    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content border-danger">
                        <div class="modal-header bg-danger text-white">
                            <h5 class="modal-title" id="deleteModalLabel">Confirm Product Deletion</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body">
                            <div class="row align-items-center">
                            <div class="col-md-4 text-center mb-3">
                                <img id="deleteProductImage" src="" alt="Product Image" class="img-thumbnail" style="max-height: 120px; object-fit: cover;">
                            </div>
                            <div class="col-md-8">
                                <h5 class="text-danger" id="deleteProductName"></h5>
                                <p id="deleteProductDescription" class="small text-muted mb-2"></p>
                                <ul class="list-group list-group-flush small">
                                <li class="list-group-item"><strong>Slug:</strong> <span id="deleteProductSlug"></span></li>
                                <li class="list-group-item"><strong>Price:</strong> ৳<span id="deleteProductPrice"></span></li>
                                <li class="list-group-item"><strong>Discount:</strong> ৳<span id="deleteProductDiscount"></span></li>
                                <li class="list-group-item"><strong>Stock:</strong> <span id="deleteProductStock"></span></li>
                                </ul>
                            </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <form method="POST" id="deleteForm">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger">Yes, Delete Product</button>
                            </form>
                        </div>
                        </div>
                    </div>
                    </div>

                <!-- Delete Confirmation Modal end-->
            </div>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted small">
                Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }} results
            </div>

            <nav>
                {{ $products->onEachSide(1)->links('pagination::bootstrap-5') }}
            </nav>
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

<!-- JS: Delete Produxt -->
<script>
  $(document).ready(function () {
    $('.delete-product-btn').on('click', function () {
      const product = $(this).data('product');
      const url = $(this).data('url');

      $('#deleteProductName').text(product.name);
      $('#deleteProductSlug').text(product.slug);
      $('#deleteProductPrice').text(parseFloat(product.price).toFixed(2));
      $('#deleteProductDiscount').text(product.discount_price ? parseFloat(product.discount_price).toFixed(2) : '0.00');
      $('#deleteProductStock').text(product.stock);
      $('#deleteProductDescription').text(product.description ? product.description.substring(0, 100) + '...' : 'No description');

      const imagePath = product.image ? `${product.image}` : '/images/default-product.jpeg';
      $('#deleteProductImage').attr('src', imagePath);

      $('#deleteForm').attr('action', url);

      $('#deleteModal').modal('show');
    });
  });
</script>

<!-- edit -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
        $('.btn-toggle-edit').on('click', function () {
        var productId = $(this).data('id');
        var editRow = $('#edit-details-' + productId);

        // Close all edit forms
        $('.details-row').not(editRow).slideUp();

        // Toggle this one
        editRow.slideToggle();
    });

</script>

<!-- Bootstrap 5 (make sure these are included once and in correct order) -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>

@endsection
