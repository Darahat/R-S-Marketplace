<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{$data['title']}} - Electronics Gadgets</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="//unpkg.com/alpinejs" defer></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- PWA -->
{{-- <link rel="manifest" href="/manifest.json"> --}}
<meta name="theme-color" content="#6e48aa">
<script>
  if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/serviceworker.js')
      .then(function() { console.log("Service Worker Registered"); });
  }
</script>
    @laravelPWA

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#6e48aa',
                        secondary: '#9d50bb',
                        accent: '#4776e6',
                        dark: '#1a1a2e',
                        light: '#f8f9fa',
                        success: '#4cc9f0',
                        warning: '#f72585',
                    },
                    animation: {
                        'float': 'float 3s ease-in-out infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' },
                        }
                    }
                }
            }
        }
    </script>
</head>
@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif
<body class="bg-gray-50">

@include('frontend_view.components.shared.navigation_bar')
    
    @yield('content')
   
@include('frontend_view.components.shared.footer')
@push('scripts')
<script>
    $(document).ready(function () {
        $(document).on('submit', '.add-to-cart-form', function (e) {
            e.preventDefault();

            let form = $(this);
            let productId = form.find('input[name="product_id"]').val();
            let qty = form.find('input[name="quantity"]').val();
            let token = $('meta[name="csrf-token"]').attr('content');

            $.ajax({
                url: "{{ route('cart.add') }}",
                method: "POST",
                data: {
                    _token: token,
                    product_id: productId,
                    quantity: qty
                },
                success: function (response) {
                    toastr.success('Product added to cart!');
                    // Update cart quantity
                    if (response.cartQuantity !== undefined) {
                        $('#cart-count').text(response.cartQuantity);
                    }
                     // Refresh cart dropdown
                    $.get("{{ route('cart.refresh') }}", function(data) {
                        $('#cart-dropdown').html(data);
                    });
                    // Optional: update cart badge
                },
                error: function () {
                    toastr.error('Something went wrong!');
                }
            });
        });
    });

    //remove item from cart
    $(document).on('click', '.remove-cart-item', function (e) {
        e.preventDefault();
        let itemId = $(this).data('id');
        let token = $('meta[name="csrf-token"]').attr('content');

        $.ajax({
            url: "{{ route('cart.remove') }}",
            type: 'POST',
            data: {
                _token: token,
                item: itemId
            },
            success: function (response) {
                toastr.success(response.success);
                $('#cart-count').text(response.totalQuantity);

                // Refresh cart view section (for cart view page)
                $.get("{{ route('cart.view.refresh') }}", function (data) {
                    $('#cart-view-section').html(data);
                });

                // Refresh the cart dropdown contents
                $.get("{{ route('cart.refresh') }}", function(data) {
                    $('#cart-dropdown').html(data);
                });
            },
            error: function () {
                toastr.error('Failed to remove product from cart.');
            }
        });
    });


    
    $(document).on('click', '.wishlist-toggle', function () {
        const productId = $(this).data('id');
        const token = $('meta[name="csrf-token"]').attr('content');
        const button = $(this);

        $.ajax({
            url: '{{ route("cart.wishlist") }}',
            method: 'POST',
            data: {
                _token: token,
                product_id: productId
            },
            success: function (response) {
                // Toggle the heart icon color
                const svg = button.find('svg');
                if (response.isWishlisted) {
                    svg.addClass('text-red-500 fill-red-500').removeClass('text-gray-400');
                } else {
                    svg.removeClass('text-red-500 fill-red-500').addClass('text-gray-400');
                }

                // Update the wishlist count
                $('#wishlist-count').text(response.count);
            },
            error: function () {
                toastr.error('Something went wrong while updating wishlist.');
            }
        });
    });

     

    // Load more products
        let page = 1;
        let loading = false;

        document.getElementById('load-more-btn').addEventListener('click', function () {
            if (loading) return;

            loading = true;
            page++;

            fetch(`?page=${page}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const newProducts = parser.parseFromString(html, 'text/html').querySelector('#product-container').innerHTML;

                document.getElementById('product-container').insertAdjacentHTML('beforeend', newProducts);

                loading = false;

                // Hide button if no more data
                if (!newProducts.trim()) {
                    document.getElementById('load-more-trigger').style.display = 'none';
                }
            });
        });

       


    
</script>
@endpush

</body>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
@stack('scripts')
</html>