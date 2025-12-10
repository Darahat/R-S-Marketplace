@extends('backend_panel_view.layouts.admin')
@section('content')

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<div class="content-wrapper">
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-plus-circle"></i> {{ $page_header }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">Products</a></li>
                        <li class="breadcrumb-item active">{{ $page_header }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-8">
                    <form action="{{ route('admin.products.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Basic Information Card -->
                        <div class="card shadow-sm mb-3">
                            <div class="card-header bg-primary text-white">
                                <h3 class="card-title mb-0"><i class="fas fa-info-circle"></i> Basic Information</h3>
                            </div>
                            <div class="card-body">
                                <!-- Product Name -->
                                <div class="mb-3">
                                    <label for="name" class="form-label">Product Name <span class="text-danger">*</span></label>
                                    <input type="text"
                                           class="form-control @error('name') is-invalid @enderror"
                                           id="name"
                                           name="name"
                                           value="{{ old('name') }}"
                                           placeholder="Enter product name"
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
                                              rows="5"
                                              placeholder="Enter product description">{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <!-- Category -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="category_id" class="form-label">Category <span class="text-danger">*</span></label>
                                            <select class="form-select @error('category_id') is-invalid @enderror"
                                                    id="category_id"
                                                    name="category_id"
                                                    required>
                                                <option value="">-- Select Category --</option>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                                        {{ $category->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('category_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Brand -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="brand_id" class="form-label">Brand</label>
                                            <select class="form-select @error('brand_id') is-invalid @enderror"
                                                    id="brand_id"
                                                    name="brand_id">
                                                <option value="">-- Select Brand --</option>
                                                @foreach($brands as $brand)
                                                    <option value="{{ $brand->id }}" {{ old('brand_id') == $brand->id ? 'selected' : '' }}>
                                                        {{ $brand->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('brand_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pricing & Stock Card -->
                        <div class="card shadow-sm mb-3">
                            <div class="card-header bg-success text-white">
                                <h3 class="card-title mb-0"><i class="fas fa-dollar-sign"></i> Pricing & Inventory</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <!-- Price -->
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="price" class="form-label">Regular Price (৳) <span class="text-danger">*</span></label>
                                            <input type="number"
                                                   class="form-control @error('price') is-invalid @enderror"
                                                   id="price"
                                                   name="price"
                                                   value="{{ old('price') }}"
                                                   min="0"
                                                   step="0.01"
                                                   placeholder="0.00"
                                                   required>
                                            @error('price')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Purchase Price -->
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="purchase_price" class="form-label">Purchase Price (৳)</label>
                                            <input type="number"
                                                   class="form-control @error('purchase_price') is-invalid @enderror"
                                                   id="purchase_price"
                                                   name="purchase_price"
                                                   value="{{ old('purchase_price', 0) }}"
                                                   min="0"
                                                   step="0.01"
                                                   placeholder="0.00">
                                            @error('purchase_price')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Discount Price -->
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="discount_price" class="form-label">Sale Price (৳)</label>
                                            <input type="number"
                                                   class="form-control @error('discount_price') is-invalid @enderror"
                                                   id="discount_price"
                                                   name="discount_price"
                                                   value="{{ old('discount_price', 0) }}"
                                                   min="0"
                                                   step="0.01"
                                                   placeholder="0.00">
                                            @error('discount_price')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- Stock -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="stock" class="form-label">Stock Quantity <span class="text-danger">*</span></label>
                                            <input type="number"
                                                   class="form-control @error('stock') is-invalid @enderror"
                                                   id="stock"
                                                   name="stock"
                                                   value="{{ old('stock', 0) }}"
                                                   min="0"
                                                   placeholder="0"
                                                   required>
                                            @error('stock')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>

                                    <!-- Rating -->
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="rating" class="form-label">Rating (0-5)</label>
                                            <input type="number"
                                                   class="form-control @error('rating') is-invalid @enderror"
                                                   id="rating"
                                                   name="rating"
                                                   value="{{ old('rating', 4.5) }}"
                                                   min="0"
                                                   max="5"
                                                   step="0.1"
                                                   placeholder="4.5">
                                            @error('rating')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <a href="{{ route('admin.products.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Back to List
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Create Product
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Right Sidebar -->
                <div class="col-lg-4">
                    <!-- Product Image Card -->
                    <div class="card shadow-sm mb-3">
                        <div class="card-header bg-info text-white">
                            <h3 class="card-title mb-0"><i class="fas fa-image"></i> Product Image</h3>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <input type="file"
                                       class="form-control @error('image') is-invalid @enderror"
                                       id="image"
                                       name="image"
                                       accept="image/*">
                                @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i> Max size: 2MB. Recommended: 800x800px
                                </small>
                            </div>

                            <!-- Image Preview -->
                            <div id="imagePreview" style="display: none;">
                                <img id="previewImg" src="" alt="Preview" class="img-fluid rounded">
                            </div>
                        </div>
                    </div>

                    <!-- Product Status Card -->
                    <div class="card shadow-sm">
                        <div class="card-header bg-warning text-dark">
                            <h3 class="card-title mb-0"><i class="fas fa-tags"></i> Product Status</h3>
                        </div>
                        <div class="card-body">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="featured" name="featured" value="1" {{ old('featured') ? 'checked' : '' }}>
                                <label class="form-check-label" for="featured">
                                    <i class="fas fa-star text-warning"></i> Featured Product
                                </label>
                            </div>

                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="is_best_selling" name="is_best_selling" value="1" {{ old('is_best_selling') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_best_selling">
                                    <i class="fas fa-fire text-danger"></i> Best Selling
                                </label>
                            </div>

                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="is_latest" name="is_latest" value="1" {{ old('is_latest') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_latest">
                                    <i class="fas fa-certificate text-info"></i> Latest Product
                                </label>
                            </div>

                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" id="is_flash_sale" name="is_flash_sale" value="1" {{ old('is_flash_sale') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_flash_sale">
                                    <i class="fas fa-bolt text-warning"></i> Flash Sale
                                </label>
                            </div>

                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" id="is_todays_deal" name="is_todays_deal" value="1" {{ old('is_todays_deal') ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_todays_deal">
                                    <i class="fas fa-calendar-day text-success"></i> Today's Deal
                                </label>
                            </div>
                        </div>
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
