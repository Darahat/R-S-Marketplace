@extends('backend_panel_view_customer.layouts.customer')

@section('title', 'My Profile')

@section('breadcrumbs')
    <nav class="flex" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('customer.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-primary">
                    <i class="fas fa-home mr-2"></i> Home
                </a>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">My Profile</span>
                </div>
            </li>
        </ol>
    </nav>
@endsection

@section('panel-content')
<div class="space-y-6">

    {{-- Profile Header --}}
    <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6 pb-6 border-b border-gray-200">
        <img class="h-24 w-24 rounded-full object-cover shadow-md"
             src="{{ $profile['profile_photo'] ? asset($profile['profile_photo']) : asset('images/default-avatar.png') }}"
             alt="Profile Photo">
        <div class="text-center sm:text-left">
            <h2 class="text-2xl font-bold text-gray-900">{{ $profile['name'] }}</h2>
            <p class="text-gray-500 mt-1">{{ $profile['email'] }}</p>
            @if($profile['mobile'])
                <p class="text-gray-500 mt-1"><i class="fas fa-phone mr-1"></i> {{ $profile['mobile'] }}</p>
            @endif
            <div class="mt-3">
                <a href="{{ route('customer.profile_setting') }}"
                   class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md bg-primary hover:bg-primary-dark text-white transition">
                    <i class="fas fa-edit mr-2"></i> Edit Profile
                </a>
            </div>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-blue-50 rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-blue-600">{{ $profile['total_orders'] }}</div>
            <div class="text-sm text-gray-600 mt-1">Total Orders</div>
        </div>
        <div class="bg-green-50 rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-green-600">৳{{ number_format($profile['total_spent'], 2) }}</div>
            <div class="text-sm text-gray-600 mt-1">Total Spent</div>
        </div>
        <div class="bg-purple-50 rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-purple-600">{{ $profile['address_count'] }}</div>
            <div class="text-sm text-gray-600 mt-1">Saved Addresses</div>
        </div>
        <div class="bg-red-50 rounded-lg p-4 text-center">
            <div class="text-2xl font-bold text-red-500">{{ $profile['wishlist_count'] }}</div>
            <div class="text-sm text-gray-600 mt-1">Wishlist Items</div>
        </div>
    </div>

    {{-- Account Details --}}
    <div class="bg-gray-50 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4"><i class="fas fa-info-circle mr-2"></i> Account Details</h3>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4">
            <div>
                <dt class="text-sm font-medium text-gray-500">Member Since</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $profile['member_since'] }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Last Login</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $profile['last_login'] }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Email</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $profile['email'] }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Phone</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $profile['mobile'] ?? 'Not provided' }}</dd>
            </div>
        </dl>
    </div>

    {{-- Quick Links --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <a href="{{ route('customer.orders') }}" class="flex items-center p-4 bg-white border border-gray-200 rounded-lg hover:shadow-md transition">
            <div class="flex-shrink-0 bg-blue-100 rounded-full p-3 mr-4">
                <i class="fas fa-shopping-bag text-blue-600"></i>
            </div>
            <div>
                <div class="font-medium text-gray-900">My Orders</div>
                <div class="text-sm text-gray-500">View order history</div>
            </div>
        </a>
        <a href="{{ route('customer.addresses.index') }}" class="flex items-center p-4 bg-white border border-gray-200 rounded-lg hover:shadow-md transition">
            <div class="flex-shrink-0 bg-green-100 rounded-full p-3 mr-4">
                <i class="fas fa-map-marker-alt text-green-600"></i>
            </div>
            <div>
                <div class="font-medium text-gray-900">Addresses</div>
                <div class="text-sm text-gray-500">Manage addresses</div>
            </div>
        </a>
        <a href="{{ route('customer.wishlist') }}" class="flex items-center p-4 bg-white border border-gray-200 rounded-lg hover:shadow-md transition">
            <div class="flex-shrink-0 bg-red-100 rounded-full p-3 mr-4">
                <i class="fas fa-heart text-red-500"></i>
            </div>
            <div>
                <div class="font-medium text-gray-900">Wishlist</div>
                <div class="text-sm text-gray-500">Saved items</div>
            </div>
        </a>
    </div>

</div>
@endsection
