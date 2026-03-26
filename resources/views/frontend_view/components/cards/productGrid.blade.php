<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 sm:gap-4">
    @foreach($products as $product)

        @include('frontend_view.components.cards.productCard',
        ['product' => $product,
        'category_name' => $product->category->name ?? ''])
    @endforeach
</div>
