<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>MarketGhor - @yield('title')</title>
    
    <!-- Favicon -->
    <link rel="icon" href="{{ asset('images/logo/favicon.png') }}">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Tailwind CSS -->
    <link href="https://unpkg.com/tailwindcss@^2/dist/tailwind.min.css" rel="stylesheet">
         <meta name="csrf-token" content="{{ csrf_token() }}">
     <script src="https://unpkg.com/alpinejs@^2.x.x" defer></script>    
    <!-- Custom CSS -->
    <style>
        .bg-primary { background-color: #4f46e5; }
        .bg-secondary { background-color: #7c3aed; }
        .text-primary { color: #4f46e5; }
        .text-secondary { color: #7c3aed; }
        .border-primary { border-color: #4f46e5; }
        .focus\:ring-primary:focus { --tw-ring-color: #4f46e5; }
        .focus\:border-primary:focus { border-color: #4f46e5; }
    </style>
</head>
<body class="font-sans antialiased">
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
@stack('scripts')
</html>