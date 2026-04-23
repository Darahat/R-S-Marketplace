<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>R&SMarketPlace - @yield('title')</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('images/logo/favicon.png') }}">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome-free/css/all.min.css') }}">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @php
        $themeConfig = config('theme', []);
        $themeVars = implode('; ', [
            '--theme-primary: ' . ($themeConfig['colors']['primary']['DEFAULT'] ?? '#3b82f6'),
            '--theme-primary-dark: ' . ($themeConfig['colors']['primary']['dark'] ?? '#2563eb'),
            '--theme-secondary: ' . ($themeConfig['colors']['secondary']['DEFAULT'] ?? '#8b5cf6'),
            '--theme-danger: ' . ($themeConfig['colors']['danger']['DEFAULT'] ?? '#ef4444'),
        ]);
    @endphp
    <link rel="stylesheet" href="{{ asset('assets/plugins/toastr/toastr.min.css') }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="font-sans antialiased" style="{{ $themeVars }}">
    @yield('content')

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="fixed bottom-4 right-4 bg-green-500 text-white px-4 py-2 rounded shadow-lg">
            {{ session('success') }}
        </div>
        <script>
            setTimeout(() => document.querySelector('.fixed').remove(), 3000);
        </script>
    @endif

    @if(session('error'))
        <div class="fixed bottom-4 right-4 bg-red-500 text-white px-4 py-2 rounded shadow-lg">
            {{ session('error') }}
        </div>
        <script>
            setTimeout(() => document.querySelector('.fixed').remove(), 3000);
        </script>
    @endif
</body>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="{{ asset('assets/plugins/toastr/toastr.min.js') }}"></script>
@stack('scripts')
</html>
