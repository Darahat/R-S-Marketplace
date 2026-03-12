<div class="row">
    <div class="col-md-6">
        <p>
            <strong><i class="fas fa-tag"></i> Name:</strong>
            {{ $category->name }}
        </p>

    <p>
        <strong><i class="fas fa-info-circle"></i> Status:</strong>
        @if($category->status)
            <span class="badge bg-success">Active</span>
        @else
            <span class="badge bg-danger">Inactive</span>
        @endif
    </p>

    <p>
        <strong><i class="fas fa-star"></i> Featured:</strong>
        @if($category->is_featured)
            <span class="badge bg-warning">Yes</span>
        @else
            <span class="badge bg-secondary">No</span>
        @endif
    </p>

    <p>
        <strong><i class="fas fa-certificate"></i> New:</strong>
        @if($category->is_new)
            <span class="badge bg-info">Yes</span>
        @else
            <span class="badge bg-secondary">No</span>
        @endif
    </p>
</div>

<div class="col-md-6">
    <p>
        <strong><i class="fas fa-sitemap"></i> Parent:</strong>
        {{ $category->parent?->name ?? 'Root Category' }}
    </p>

    <p>
        <strong><i class="fas fa-box"></i> Products:</strong>
        <span class="badge bg-info">
            {{ $category->products_count ?? $category->products->count() }}
        </span>
    </p>

    <p>
        <strong><i class="fas fa-percentage"></i> Discount:</strong>
        {{ $category->discount_price ?? 0 }}%
    </p>

    <p>
        <strong><i class="fas fa-calendar"></i> Created:</strong>
        {{ $category->created_at->format('M d, Y h:i A') }}
    </p>
</div>


</div>

@if($category->description)

<hr>
<p>
    <strong><i class="fas fa-align-left"></i> Description:</strong>
</p>
<p>{!! nl2br(e($category->description)) !!}</p>
@endif

@if($category->children->count())

<hr>
<p>
<strong>
<i class="fas fa-folder-tree"></i>
Subcategories ({{ $category->children->count() }})
</strong>
</p>

<ul>
@foreach($category->children as $child)
    <li>{{ $child->name }}</li>
@endforeach
</ul>
@endif

@if($category->creator)

<hr>
<p class="small text-muted mb-0">
<strong>Created by:</strong> {{ $category->creator->name }}
</p>
@endif

@if($category->updater)

<p class="small text-muted mb-0">
<strong>Last updated by:</strong> {{ $category->updater->name }}
</p>
@endif
