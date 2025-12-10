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
                        <li class="breadcrumb-item"><a href="{{ route('admin.categories.index') }}">Categories</a></li>
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
                    <div class="card shadow-sm">
                        <div class="card-header bg-warning text-dark">
                            <h3 class="card-title mb-0"><i class="fas fa-edit"></i> Edit Category Information</h3>
                        </div>

                        <form action="{{ route('admin.categories.update', $category->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="card-body">
                                <!-- Category Name -->
                                <div class="mb-3">
                                    <label for="name" class="form-label">Category Name <span class="text-danger">*</span></label>
                                    <input type="text"
                                           class="form-control @error('name') is-invalid @enderror"
                                           id="name"
                                           name="name"
                                           value="{{ old('name', $category->name) }}"
                                           placeholder="Enter category name"
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Description -->
                                <div class="mb-3">
                                    <label for="description" class="form-label">Description</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror"
                                              id="description"
                                              name="description"
                                              rows="4"
                                              placeholder="Enter category description">{{ old('description', $category->description) }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Parent Category -->
                                <div class="mb-3">
                                    <label for="parent_id" class="form-label">Parent Category</label>
                                    <select class="form-select @error('parent_id') is-invalid @enderror"
                                            id="parent_id"
                                            name="parent_id">
                                        <option value="">-- No Parent (Root Category) --</option>
                                        @foreach($categories as $cat)
                                            @if($cat->id != $category->id)
                                                <option value="{{ $cat->id }}"
                                                    {{ old('parent_id', $category->parent_id) == $cat->id ? 'selected' : '' }}>
                                                    {{ str_repeat('—', $cat->level ?? 0) }} {{ $cat->name }}
                                                </option>
                                            @endif
                                        @endforeach
                                    </select>
                                    @error('parent_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> Cannot select itself as parent
                                    </small>
                                </div>

                                <!-- Discount Price -->
                                <div class="mb-3">
                                    <label for="discount_price" class="form-label">Category Discount (%)</label>
                                    <input type="number"
                                           class="form-control @error('discount_price') is-invalid @enderror"
                                           id="discount_price"
                                           name="discount_price"
                                           value="{{ old('discount_price', $category->discount_price ?? 0) }}"
                                           min="0"
                                           max="100"
                                           step="0.01"
                                           placeholder="0.00">
                                    @error('discount_price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> Applies to all products in this category
                                    </small>
                                </div>

                                <!-- Current Image -->
                                @if($category->image)
                                <div class="mb-3">
                                    <label class="form-label">Current Image</label><br>
                                    <img src="{{ asset('storage/' . $category->image) }}"
                                         alt="{{ $category->name }}"
                                         class="img-thumbnail"
                                         style="max-width: 200px;">
                                </div>
                                @endif

                                <!-- Image Upload -->
                                <div class="mb-3">
                                    <label for="image" class="form-label">
                                        {{ $category->image ? 'Change' : 'Upload' }} Category Image
                                    </label>
                                    <input type="file"
                                           class="form-control @error('image') is-invalid @enderror"
                                           id="image"
                                           name="image"
                                           accept="image/*">
                                    @error('image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> Recommended size: 400x400px. Leave empty to keep current image.
                                    </small>
                                </div>

                                <!-- Image Preview -->
                                <div class="mb-3" id="imagePreview" style="display: none;">
                                    <label class="form-label">New Image Preview</label><br>
                                    <img id="previewImg" src="" alt="Preview" class="img-thumbnail" style="max-width: 200px;">
                                </div>

                                <!-- Checkboxes Row -->
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   id="status"
                                                   name="status"
                                                   value="1"
                                                   {{ old('status', $category->status) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="status">
                                                <i class="fas fa-toggle-on text-success"></i> Active Status
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   id="is_featured"
                                                   name="is_featured"
                                                   value="1"
                                                   {{ old('is_featured', $category->is_featured) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_featured">
                                                <i class="fas fa-star text-warning"></i> Featured
                                            </label>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   id="is_new"
                                                   name="is_new"
                                                   value="1"
                                                   {{ old('is_new', $category->is_new) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="is_new">
                                                <i class="fas fa-certificate text-info"></i> New Category
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Metadata -->
                                <div class="alert alert-info">
                                    <h6 class="mb-2"><i class="fas fa-info-circle"></i> Category Information</h6>
                                    <small>
                                        <strong>Created:</strong> {{ $category->created_at->format('M d, Y h:i A') }}<br>
                                        <strong>Last Updated:</strong> {{ $category->updated_at->format('M d, Y h:i A') }}<br>
                                        @if($category->creator)
                                            <strong>Created By:</strong> {{ $category->creator->name }}<br>
                                        @endif
                                        @if($category->updater)
                                            <strong>Updated By:</strong> {{ $category->updater->name }}<br>
                                        @endif
                                        <strong>Total Products:</strong> {{ $category->products->count() }}
                                    </small>
                                </div>
                            </div>

                            <div class="card-footer bg-light">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Back to List
                                    </a>
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-save"></i> Update Category
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Image preview
    $('#image').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#previewImg').attr('src', e.target.result);
                $('#imagePreview').show();
            }
            reader.readAsDataURL(file);
        } else {
            $('#imagePreview').hide();
        }
    });
});
</script>
@endpush

@endsection
