{{-- filepath: resources/views/layouts/admin.blade.php --}}
<!DOCTYPE html>
<html>
<head>
    <title>@yield('title') - Admin Panel</title>
    {{-- Admin specific CSS --}}
</head>
<body class="admin-panel">
    @include('partials.admin.sidebar')

    <main class="content">
        @include('partials.admin.header')
        @yield('content')
    </main>

    @include('partials.admin.footer')
</body>
</html>
