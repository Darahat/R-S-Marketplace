<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{$data['title']}} - Electronics Gadgets</title>
@vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/toastr/toastr.min.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- PWA -->
    @php
        $themeConfig = config('theme', []);
        $themeVars = implode('; ', [
            '--theme-primary: ' . ($themeConfig['colors']['primary']['DEFAULT'] ?? '#3b82f6'),
            '--theme-primary-light: ' . ($themeConfig['colors']['primary']['light'] ?? '#60a5fa'),
            '--theme-primary-dark: ' . ($themeConfig['colors']['primary']['dark'] ?? '#2563eb'),
            '--theme-secondary: ' . ($themeConfig['colors']['secondary']['DEFAULT'] ?? '#8b5cf6'),
            '--theme-secondary-light: ' . ($themeConfig['colors']['secondary']['light'] ?? '#a78bfa'),
            '--theme-secondary-dark: ' . ($themeConfig['colors']['secondary']['dark'] ?? '#7c3aed'),
            '--theme-success: ' . ($themeConfig['colors']['success']['DEFAULT'] ?? '#10b981'),
            '--theme-warning: ' . ($themeConfig['colors']['warning']['DEFAULT'] ?? '#f59e0b'),
            '--theme-danger: ' . ($themeConfig['colors']['danger']['DEFAULT'] ?? '#ef4444'),
            '--theme-accent: ' . ($themeConfig['colors']['primary']['light'] ?? '#60a5fa'),
        ]);
    @endphp
    <meta name="theme-color" content="{{ $themeConfig['colors']['primary']['DEFAULT'] ?? '#3b82f6' }}">
        <link rel="manifest" href="{{ asset('manifest.json') }}">
    <script>
      if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/serviceworker.js')
          .then(function() { console.log("Service Worker Registered"); });
      }
    </script>

    <style>
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
<body x-data="{}" class="bg-gray-50" style="{{ $themeVars }}">
@include('frontend_view.components.shared.navigation_bar')

    @yield('content')

@include('frontend_view.components.shared.footer')
@push('scripts')
<script>
     function refreshNavCartDropdown() {
         const navEl = document.querySelector('[x-data="navbar()"]');
         if (navEl) {
             // This is the official Alpine v3 way to talk to a component
             const alpineData = Alpine.$data(navEl);
             if (alpineData) {
                 alpineData.refreshCart();
             }
         }
    }

    function refreshNavWishlistCount() {
        const navEl = document.querySelector('[x-data="navbar()"]');
        if (navEl) {
            const alpineData = Alpine.$data(navEl);
            if (alpineData) {
                alpineData.refreshWishlist();
            }
        }
    }

    function formatPrice(value) {
        const num = Number(value || 0);
        if (Number.isNaN(num)) return '0.00';
        return num.toFixed(2);
    }

    function parseMoney(text) {
        const value = parseFloat((text || '').toString().replace(/[^0-9.]/g, ''));
        return Number.isNaN(value) ? 0 : value;
    }

    function setButtonLoading(button, isLoading, loadingHtml = null) {
        const $button = $(button);
        if (!$button.length) return;

        if (isLoading) {
            if ($button.data('loading') === true) {
                return;
            }
            $button.data('loading', true);
            $button.data('original-html', $button.html());
            $button.prop('disabled', true).addClass('opacity-60 cursor-not-allowed');
            if (loadingHtml) {
                $button.html(loadingHtml);
            }
            return;
        }

        const original = $button.data('original-html');
        if (original !== undefined) {
            $button.html(original);
        }
        $button.prop('disabled', false).removeClass('opacity-60 cursor-not-allowed');
        $button.data('loading', false);
    }

    function setIconLoading(button, isLoading) {
        const $button = $(button);
        if (!$button.length) return;

        if (isLoading) {
            if ($button.data('icon-loading') === true) {
                return;
            }
            $button.data('icon-loading', true);
            $button.prop('disabled', true).addClass('opacity-70 cursor-not-allowed');
            if (!$button.find('.inline-loading-indicator').length) {
                $button.append('<span class="inline-loading-indicator absolute -bottom-1 -right-1 text-[10px] bg-white rounded-full px-1"><i class="fas fa-spinner fa-spin text-primary"></i></span>');
            }
            return;
        }

        $button.find('.inline-loading-indicator').remove();
        $button.prop('disabled', false).removeClass('opacity-70 cursor-not-allowed');
        $button.data('icon-loading', false);
    }



    $(document).ready(function () {
        $(document).on('submit', '.add-to-cart-form', function (e) {
            e.preventDefault();

            let form = $(this);
            let submitBtn = form.find('button[type="submit"]').first();
            if (submitBtn.data('loading') === true) {
                return;
            }

            let productId = form.find('input[name="product_id"]').val();
            let qty = form.find('input[name="quantity"]').val();
            let token = $('meta[name="csrf-token"]').attr('content');

            setButtonLoading(submitBtn, true, '<i class="fas fa-spinner fa-spin mr-1.5"></i><span class="hidden sm:inline">Adding...</span>');

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
                    refreshNavCartDropdown();
                },
                error: function () {
                    toastr.error('Something went wrong!');
                },
                complete: function () {
                    setButtonLoading(submitBtn, false);
                }
            });
        });
    });

    //remove item from cart
    $(document).on('click', '.remove-cart-item', function (e) {
        e.preventDefault();
        const removeBtn = $(this);
        if (removeBtn.data('loading') === true) {
            return;
        }

        setButtonLoading(removeBtn, true, '<i class="fas fa-spinner fa-spin"></i>');

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

                // Refresh cart view section (for cart view page)
                $.get("{{ route('cart.view.refresh') }}", function (data) {
                    $('#cart-view-section').html(data);
                });

                refreshNavCartDropdown();
            },
            error: function () {
                toastr.error('Failed to remove product from cart.');
            },
            complete: function () {
                setButtonLoading(removeBtn, false);
            }
        });
    });



    $(document).on('click', '.wishlist-toggle', function () {
        const productId = $(this).data('id');
        const token = $('meta[name="csrf-token"]').attr('content');
        const button = $(this);
        const svg = button.find('svg');
        const wasWishlisted = svg.hasClass('fill-danger') || svg.attr('fill') === 'currentColor';
        const desiredState = !wasWishlisted;

        if (button.data('loading') === true) {
            return;
        }
        setIconLoading(button, true);

        // Optimistic icon update for immediate UX.
        if (desiredState) {
            svg.addClass('text-danger fill-danger').removeClass('text-gray-400');
            svg.attr('fill', 'currentColor');
            svg.find('path').attr('stroke-width', '0');
        } else {
            svg.removeClass('text-danger fill-danger').addClass('text-gray-400');
            svg.attr('fill', 'none');
            svg.find('path').attr('stroke-width', '1.5');
        }

        $.ajax({
            url: '{{ route("wishlist.toggle") }}',
            method: 'POST',
            data: {
                _token: token,
                product_id: productId,
                desired_state: desiredState
            },
            success: function (response) {
                if (typeof response.is_wishlisted === 'boolean') {
                    if (response.is_wishlisted) {
                        svg.addClass('text-danger fill-danger').removeClass('text-gray-400');
                        svg.attr('fill', 'currentColor');
                        svg.find('path').attr('stroke-width', '0');
                    } else {
                        svg.removeClass('text-danger fill-danger').addClass('text-gray-400');
                        svg.attr('fill', 'none');
                        svg.find('path').attr('stroke-width', '1.5');
                    }
                }

                if (response.count !== undefined && response.count !== null) {
                    refreshNavWishlistCount();
                }
            },
            error: function () {
                // Roll back optimistic state on failure.
                if (wasWishlisted) {
                    svg.addClass('text-danger fill-danger').removeClass('text-gray-400');
                    svg.attr('fill', 'currentColor');
                    svg.find('path').attr('stroke-width', '0');
                } else {
                    svg.removeClass('text-danger fill-danger').addClass('text-gray-400');
                    svg.attr('fill', 'none');
                    svg.find('path').attr('stroke-width', '1.5');
                }

                if (typeof toastr !== 'undefined') {
                    toastr.error('Something went wrong while updating wishlist.');
                } else {
                    alert('Something went wrong while updating wishlist.');
                }
            },
            complete: function () {
                setIconLoading(button, false);
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

@stack('scripts')
</body>
</html>
