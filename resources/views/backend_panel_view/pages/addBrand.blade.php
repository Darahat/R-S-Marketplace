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
                <h3 class="card-title mb-0">Brand List</h3>
                <input type="text" id="brandSearch" class="form-control form-control-sm w-50" placeholder="Search brands...">
            </div>

            <div class="card-body table-responsive p-0">
                <table id="brandTable" class="table table-hover table-striped text-nowrap align-middle mb-0">
                    <thead class="bg-light text-dark">
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 25%;">Name</th>
                        <th style="width: 25%;">Slug</th>
                        <th style="width: 15%;">Status</th>
                        <th style="width: 30%;" class="text-center">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                        @forelse ($brands as $index => $brand)
                            <tr class="main-row" data-id="{{ $brand->id }}">
                                <td style="width: 5%;">{{ $brands->firstItem() + $index }}</td>
                                <td style="width: 25%;"><strong>{{ $brand->name }}</strong></td>
                                <td style="width: 25%;"><strong>{{ $brand->slug }}</strong></td>
                                <td style="width: 15%;">
                                    @if($brand->status > 0)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-danger">Inactive</span>
                                    @endif
                                </td>

                                <td style="width: 10%;">
                                    <button class="btn btn-sm btn-info btn-toggle-details" data-id="{{ $brand->id }}"><i class="fas fa-eye"></i></button>
                                </td>
                                <td style="width: 10%;">
                                    <button class="btn btn-sm btn-warning btn-toggle-edit" data-id="{{ $brand->id }}">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                </td>
                                <td style="width: 10%;">
                                    <button class="btn btn-sm btn-danger delete-brand-btn"
                                        data-brand='@json($brand)'
                                        data-url="{{ route('brands.destroy', $brand->id) }}">
                                        <i class="fas fa-trash-alt"></i> Delete
                                    </button>
                                </td>
                            </tr>

                            <!-- Details View -->
                            <tr class="details-row bg-light" id="details-{{ $brand->id }}" style="display: none;">
                                <td colspan="7" class="p-0">
                                    <div class="p-4 bg-white border-top">
                                        <h5 class="mb-3 text-primary">Brand Details</h5>
                                        <div class="row mb-4 align-items-start">
                                            <div class="col-md-3 text-center mb-3">
                                                <div class="border rounded p-2 bg-light">
                                                    @if($brand->image_url)
                                                        <img src="{{ $brand->image_url }}" alt="Brand Image" class="img-fluid rounded shadow-sm" style="max-height: 120px; max-width: 100px; object-fit: cover;">
                                                    @else
                                                        <img src="{{ asset('images/default-product.jpeg') }}" alt="No Image" class="img-fluid rounded shadow-sm" style="max-height: 120px; max-width: 100px; object-fit: cover;">
                                                        <div class="text-muted small mt-2">No image available</div>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-md-9">
                                                <div class="row mb-2">
                                                    <div class="col-md-4 mb-2">
                                                        <strong>Name:</strong><br>{{ $brand->name }}
                                                    </div>
                                                    <div class="col-md-4 mb-2">
                                                        <strong>Slug:</strong><br>{{ $brand->slug }}
                                                    </div>
                                                    <div class="col-md-4 mb-2">
                                                        <strong>Status:</strong><br>{{ $brand->status }}
                                                    </div>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6 mb-2">
                                                        <strong>Created At:</strong><br>
                                                        {{ $brand->created_at ? $brand->created_at->format('d M, Y h:i A') : 'N/A' }}
                                                    </div>
                                                    <div class="col-md-6 mb-2">
                                                        <strong>Updated At:</strong><br>
                                                        {{ $brand->updated_at ? $brand->updated_at->format('d M, Y h:i A') : 'N/A' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <!-- Edit View -->
                            <tr class="details-row" id="edit-details-{{ $brand->id }}" style="display: none;">
                                <td colspan="7" class="p-0">
                                    <form action="#" method="POST" enctype="multipart/form-data" class="p-3 bg-light border">
                                        @csrf
                                        @method('PUT')
                                        <div class="row">
                                            <div class="col-md-4 mb-2">
                                                <label><strong>Name</strong></label>
                                                <input type="text" name="name" value="{{ $brand->name }}" class="form-control">
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label><strong>Slug</strong></label>
                                                <input type="text" name="slug" value="{{ $brand->slug }}" class="form-control">
                                            </div>
                                            <div class="col-md-4 mb-2">
                                                <label><strong>Status</strong></label>
                                                <select name="status" class="form-control">
                                                    <option value="1" {{ $brand->status == 1 ? 'selected' : '' }}>Active</option>
                                                    <option value="0" {{ $brand->status == 0 ? 'selected' : '' }}>Inactive</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <button type="submit" class="btn btn-success btn-sm">Update Brand</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No brands found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <!-- Delete Modal -->
                <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-md">
                        <div class="modal-content border-danger">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title" id="deleteModalLabel">Confirm Brand Deletion</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="text-center">
                                    <img id="deleteBrandImage" src="" alt="Brand Image" class="img-thumbnail mb-2" style="max-height: 100px; object-fit: cover;">
                                    <h5 id="deleteBrandName"></h5>
                                    <p class="small text-muted">Slug: <span id="deleteBrandSlug"></span></p>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <form method="POST" id="deleteForm">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-danger">Yes, Delete Brand</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Pagination -->

        <div class="d-flex justify-content-between align-items-center mt-4">
            <div class="text-muted small">
                Showing {{ $brands->firstItem() }} to {{ $brands->lastItem() }} of {{ $brands->total() }} results
            </div>

            <nav>
                {{ $brands->onEachSide(1)->links('pagination::bootstrap-5') }}
            </nav>
        </div>



    </section>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.getElementById('brandSearch');
        const table = document.getElementById('brandTable');
        const rows = table.querySelectorAll('tbody tr.main-row');

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

        document.querySelectorAll('.btn-toggle-details').forEach(button => {
            button.addEventListener('click', function () {
                const id = this.dataset.id;
                const row = document.getElementById('details-' + id);
                row.style.display = row.style.display === 'none' ? '' : 'none';
            });
        });

        $('.btn-toggle-edit').on('click', function () {
            const brandId = $(this).data('id');
            const editRow = $('#edit-details-' + brandId);
            $('.details-row').not(editRow).slideUp();
            editRow.slideToggle();
        });

        $('.delete-brand-btn').on('click', function () {
            const brand = $(this).data('brand');
            const url = $(this).data('url');

            $('#deleteBrandName').text(brand.name);
            $('#deleteBrandSlug').text(brand.slug);
            const imagePath = brand.image_url ? `${brand.image_url}` : '/images/default-product.jpeg';
            $('#deleteBrandImage').attr('src', imagePath);
            $('#deleteForm').attr('action', url);
            $('#deleteModal').modal('show');
        });
    });
</script>

<!-- Bootstrap -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.min.js"></script>

@endsection
