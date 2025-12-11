<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 sm:gap-4">
    @foreach($products as $product)
        @php
            // Get category name from database using category_id
            $categoryName = '';
            if($product->category_id) {
                $category = DB::table('categories')->where('id', $product->category_id)->first();
                $categoryName = $category ? $category->name : '';
            }
        @endphp
        @include('frontend_view.components.cards.productCard', ['product' => $product, 'category_name' => $categoryName])
    @endforeach
</div>
