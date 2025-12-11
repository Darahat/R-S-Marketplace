@extends('backend_panel_view.layouts.admin')
@section('content')

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-edit"></i> {{ $page_header }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.brands.index') }}">Brands</a></li>
                        <li class="breadcrumb-item active">{{ $page_header }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <form action="{{ route('admin.brands.update', $brand->id) }}" method="POST" id="brandForm">
                        @csrf
                        @method('PUT')

                        <!-- Basic Information Card -->
                        <div class="card shadow-sm mb-3">
                            <div class="card-header bg-warning text-white">
                                <h3 class="card-title mb-0"><i class="fas fa-info-circle"></i> Brand Information</h3>
                            </div>
                            <div class="card-body">
                                <!-- Brand Name -->
                                <div class="mb-3">
                                    <label for="name" class="form-label">Brand Name <span class="text-danger">*</span></label>
                                    <input type="text"
                                           class="form-control @error('name') is-invalid @enderror"
                                           id="name"
                                           name="name"
                                           value="{{ old('name', $brand->name) }}"
                                           placeholder="Enter brand name (e.g., Nike, Apple, Samsung)"
                                           required>
                                    <div class="form-text">The brand name will be displayed to customers.</div>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Slug (Auto-generated) -->
                                <div class="mb-3">
                                    <label for="slug" class="form-label">Slug <span class="text-muted">(Auto-generated)</span></label>
                                    <input type="text"
                                           class="form-control"
                                           id="slug"
                                           value="{{ $brand->slug }}"
                                           placeholder="brand-name-slug"
                                           readonly
                                           disabled>
                                    <div class="form-text">URL-friendly version of the brand name (automatically generated).</div>
                                </div>

                                <!-- Categories -->
                                <div class="mb-3">
                                    <label class="form-label">Associated Categories <span class="text-muted">(Optional)</span></label>
                                    <div class="form-text mb-2">Select categories where this brand's products will appear. Leave empty for all categories.</div>
                                    <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                                        @if($categories->count() > 0)
                                            @foreach($categories as $category)
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input"
                                                           type="checkbox"
                                                           name="category_id[]"
                                                           value="{{ $category->id }}"
                                                           id="category{{ $category->id }}"
                                                           {{ in_array($category->id, $selectedCategories) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="category{{ $category->id }}">
                                                        {{ $category->name }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        @else
                                            <p class="text-muted mb-0">No categories available.</p>
                                        @endif
                                    </div>
                                </div>

                                <!-- Status -->
                                <div class="mb-3">
                                    <label class="form-label">Status <span class="text-danger">*</span></label>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               role="switch"
                                               id="status"
                                               name="status"
                                               value="1"
                                               {{ old('status', $brand->status) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="status">
                                            <span class="active-text" style="{{ old('status', $brand->status) ? '' : 'display: none;' }}">Active</span>
                                            <span class="inactive-text" style="{{ old('status', $brand->status) ? 'display: none;' : '' }}">Inactive</span>
                                        </label>
                                    </div>
                                    <div class="form-text">Only active brands will be visible to customers.</div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-save"></i> Update Brand
                                    </button>
                                    <a href="{{ route('admin.brands.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                    <button type="button" class="btn btn-danger ms-auto" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                        <i class="fas fa-trash"></i> Delete Brand
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
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
                <p>Are you sure you want to delete "<strong>{{ $brand->name }}</strong>"?</p>
                <p class="text-danger"><small><i class="fas fa-info-circle"></i> This action cannot be undone!</small></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('admin.brands.destroy', $brand->id) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Yes, Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    // Auto-generate slug from name
    $('#name').on('input', function() {
        let name = $(this).val();
        let slug = name.toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
        $('#slug').val(slug);
    });

    // Toggle status label
    $('#status').on('change', function() {
        if($(this).is(':checked')) {
            $('.active-text').show();
            $('.inactive-text').hide();
        } else {
            $('.active-text').hide();
            $('.inactive-text').show();
        }
    });

    // Form validation
    $('#brandForm').on('submit', function(e) {
        let name = $('#name').val().trim();

        if(!name) {
            e.preventDefault();
            alert('Please enter a brand name');
            $('#name').focus();
            return false;
        }
    });
});
</script>
@endpush

@endsection
