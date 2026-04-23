<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Electronics Gadgets - Customer Panel</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/toastr/toastr.min.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
        ]);
    @endphp

    <!-- PWA -->
    <meta name="theme-color" content="{{ $themeConfig['colors']['primary']['DEFAULT'] ?? '#3b82f6' }}">
    <script>
      if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/serviceworker.js')
          .then(function() { console.log("Service Worker Registered"); });
      }
    </script>
    @laravelPWA

</head>
<body class="h-full bg-primary-50 text-gray-800" x-data="{ mobileMenuOpen: false }" style="{{ $themeVars }}">
    @if(session('message'))
    <script>
        showToast('success', '{{ session('message') }}');
    </script>
@endif
    @auth
        <!-- Authenticated User Layout -->
        <div class="flex h-screen bg-primary-50">
            <!-- Side Panel -->
            @include('backend_panel_view_customer.components.shared.sidepanel')

            <div class="flex flex-col flex-1 overflow-hidden">
                <!-- Top Navigation -->
                @include('backend_panel_view_customer.components.shared.topnav')

                <!-- Main Content -->
                <main class="flex-1 overflow-y-auto p-6 bg-primary-100">
                    <div class="max-w-7xl mx-auto">
                        <!-- Page Header -->
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-2xl font-bold text-primary-800">@yield('title')</h2>
                            @yield('breadcrumbs')
                        </div>

                        <!-- Content -->
                        <div class="bg-white rounded-lg shadow-md p-6">
                            @yield('panel-content')
                        </div>
                    </div>
                </main>
            </div>
        </div>
         <!-- Mobile Menu (outside main flex container) -->
        @include('backend_panel_view_customer.components.shared.mobile_menu')
    @else
        <!-- Guest Layout -->
        <div class="min-h-screen bg-primary-50">
            <!-- Page Content -->
            @yield('content')
        </div>
    @endauth

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            {{ session('success') }}
            <button class="ml-4" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="fixed bottom-4 right-4 bg-red-500 text-white px-4 py-2 rounded-lg shadow-lg flex items-center">
            <i class="fas fa-exclamation-circle mr-2"></i>
            {{ session('error') }}
            <button class="ml-4" onclick="this.parentElement.remove()">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    <!-- Loading Indicator -->
    <div id="loading" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center">
        <div class="bg-white p-6 rounded-lg shadow-xl text-center">
            <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-primary mx-auto"></div>
            <p class="mt-4 text-gray-700">Loading...</p>
        </div>
    </div>

    <script>
        // Global loading indicator
        document.addEventListener('DOMContentLoaded', function() {
            const loadingIndicator = document.getElementById('loading');

            const showLoading = function() {
                loadingIndicator?.classList.remove('hidden');
                loadingIndicator?.classList.add('flex');
            };

            const hideLoading = function() {
                loadingIndicator?.classList.remove('flex');
                loadingIndicator?.classList.add('hidden');
            };

            hideLoading();

            window.addEventListener('load', hideLoading);
            window.addEventListener('pageshow', hideLoading);

            // Show loading only on actual navigations (not F5/Ctrl+R reload)
            document.addEventListener('click', function(e) {
                const link = e.target.closest('a[href]:not([href^="#"]):not([href^="javascript"])');
                if (link && !link.target && !link.hasAttribute('data-no-loading')) {
                    showLoading();
                }
            });
            document.addEventListener('submit', function(e) {
                if (!e.target.hasAttribute('data-no-loading')) {
                    showLoading();
                }
            });

            // Toggle password visibility
            window.togglePassword = function(id) {
                const input = document.getElementById(id);
                const icon = event.currentTarget.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.replace('fa-eye', 'fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.replace('fa-eye-slash', 'fa-eye');
                }
            }

            // Auto-hide flash messages after 5 seconds
            setTimeout(() => {
                const flashes = document.querySelectorAll('[class*="fixed bottom-4"]');
                flashes.forEach(flash => flash.remove());
            }, 5000);
        });
        function showToast(type, message) {
            const toast = $(`
                <div class="fixed top-4 right-4 z-50 px-5 py-3 rounded-md shadow-lg text-white bg-${type === 'success' ? 'green' : 'red'}-500 animate-fade-in">
                    <div class="flex items-center">
                        <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} mr-2"></i>
                        ${message}
                    </div>
                </div>
            `);
            $('body').append(toast);
            setTimeout(() => {
                toast.remove();
            }, 3000);
        }
    </script>
    <!-- In your customer layout, before </body> -->
<script>
    window.userId = {{ auth()->id() ?? 'null' }};
</script>
@vite(['resources/js/notifications.js'])
</body>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('assets/plugins/toastr/toastr.min.js') }}"></script>
@stack('scripts')
</html>
