<div class="tree-item" style="padding-left: {{ $level * 20 }}px;">
    <div class="d-flex align-items-center py-2 tree-node" data-id="{{ $category->id }}">
        @if($category->children->count() > 0)
            <span class="toggle-icon me-2" style="cursor: pointer;">
                <i class="fas fa-chevron-right"></i>
            </span>
        @else
            <span class="me-2" style="width: 16px; display: inline-block;"></span>
        @endif

        <i class="fas fa-folder me-2 text-warning"></i>

        <span class="flex-grow-1">{{ $category->name }}</span>

        <span class="badge bg-secondary ms-2">{{ $category->products->count() }}</span>

        @if($category->status)
            <i class="fas fa-check-circle text-success ms-2" title="Active"></i>
        @else
            <i class="fas fa-times-circle text-danger ms-2" title="Inactive"></i>
        @endif
    </div>

    <div class="tree-children" style="display: none;">
        @foreach($category->children as $child)
            @include('backend_panel_view.pages.categories.partials.tree_item', ['category' => $child, 'level' => $level + 1])
        @endforeach
    </div>
</div>

@once
@push('scripts')
<script>
$(document).on('click', '.toggle-icon', function() {
    const $icon = $(this).find('i');
    const $children = $(this).closest('.tree-item').find('> .tree-children');

    if ($children.is(':visible')) {
        $children.slideUp(200);
        $icon.removeClass('fa-chevron-down').addClass('fa-chevron-right');
    } else {
        $children.slideDown(200);
        $icon.removeClass('fa-chevron-right').addClass('fa-chevron-down');
    }
});
</script>
@endpush
@endonce
