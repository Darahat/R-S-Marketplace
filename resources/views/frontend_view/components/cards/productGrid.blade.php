@foreach($products as $product)
    @include('frontend_view.components.cards.productCard', ['product' => $product, 'category_name' => $category_name])
@endforeach
