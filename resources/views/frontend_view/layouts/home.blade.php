<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{$data['title']}} - Electronics Gadgets</title>


    <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/toastr/toastr.min.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- PWA -->
    <meta name="theme-color" content="{{ config('theme.colors.primary.DEFAULT', '#3b82f6') }}">
        <link rel="manifest" href="{{ asset('manifest.json') }}">
    <script>
      if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/serviceworker.js')
          .then(function() { console.log("Service Worker Registered"); });
      }
    </script>

    <style>
        :root {
            --theme-primary: {{ config('theme.colors.primary.DEFAULT', '#3b82f6') }};
            --theme-primary-light: {{ config('theme.colors.primary.light', '#60a5fa') }};
            --theme-primary-dark: {{ config('theme.colors.primary.dark', '#2563eb') }};
            --theme-secondary: {{ config('theme.colors.secondary.DEFAULT', '#8b5cf6') }};
            --theme-secondary-light: {{ config('theme.colors.secondary.light', '#a78bfa') }};
            --theme-secondary-dark: {{ config('theme.colors.secondary.dark', '#7c3aed') }};
            --theme-success: {{ config('theme.colors.success.DEFAULT', '#10b981') }};
            --theme-warning: {{ config('theme.colors.warning.DEFAULT', '#f59e0b') }};
            --theme-danger: {{ config('theme.colors.danger.DEFAULT', '#ef4444') }};
            --theme-accent: {{ config('theme.colors.primary.light', '#60a5fa') }};
        }

        * {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        .btn-primary {
            @apply bg-primary hover:bg-primary-dark transition-all duration-300;
            transform: translateY(0);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .card-hover {
            transition: all 300ms ease;
        }

        .card-hover:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.1);
        }

        html {
            scroll-behavior: smooth;
        }

        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #3b82f6;
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #2563eb;
        }

        @keyframes shimmer {
            0% { background-position: -1000px 0; }
            100% { background-position: 1000px 0; }
        }

        .animate-shimmer {
            animation: shimmer 2s infinite linear;
            background: linear-gradient(to right, #f6f7f8 0%, #edeef1 20%, #f6f7f8 40%, #f6f7f8 100%);
            background-size: 1000px 100%;
        }
    </style>
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
            url: '{{ route("wishlist.toggle") }}',
            method: 'POST',
            data: {
                _token: token,
                product_id: productId
            },
            success: function (response) {
                // Toggle the heart icon color
                const svg = button.find('svg');
                if (response.is_wishlisted) {
                    svg.addClass('text-danger fill-danger').removeClass('text-gray-400');
                    svg.attr('fill', 'currentColor');
                    svg.find('path').attr('stroke-width', '0');
                } else {
                    svg.removeClass('text-danger fill-danger').addClass('text-gray-400');
                    svg.attr('fill', 'none');
                    svg.find('path').attr('stroke-width', '1.5');
                }

                // Update the wishlist count
                $('#wishlist-count').text(response.count);
            },
            error: function () {
                if (typeof toastr !== 'undefined') {
                    toastr.error('Something went wrong while updating wishlist.');
                } else {
                    alert('Something went wrong while updating wishlist.');
                }
            }
        });
    });


        // Load more products
        const loadMoreBtn = document.getElementById('load-more-btn');
        if (loadMoreBtn) {
            let page = 1;
            let loading = false;

            loadMoreBtn.addEventListener('click', function () {
                if (loading) return;

                loading = true;
                page++;

                fetch(`?page=${page}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const container = parser.parseFromString(html, 'text/html').querySelector('#product-container');
                    if (container) {
                        document.getElementById('product-container').insertAdjacentHTML('beforeend', container.innerHTML);
                    }
                    loading = false;
                    const trigger = document.getElementById('load-more-trigger');
                    if (trigger && (!container || !container.innerHTML.trim())) {
                        trigger.style.display = 'none';
                    }
                });
            });
        }





</script>
@endpush

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('assets/plugins/toastr/toastr.min.js') }}"></script>
@vite(['resources/css/app.css', 'resources/js/app.js'])
@stack('scripts')
</body>
</html>
