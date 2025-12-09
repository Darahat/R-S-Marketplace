@extends('backend_panel_view_customer.layouts.customer')

@section('title', 'Address Book')

@section('breadcrumbs')
    <nav class="flex" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('customer.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-primary">
                    <i class="fas fa-home mr-2"></i>
                    Home
                </a>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Address Book</span>
                </div>
            </li>
        </ol>
    </nav>
@endsection

@section('panel-content')
<div class="bg-white rounded-lg shadow-sm overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
        <div>
            <h1 class="text-xl font-semibold text-gray-800">Address Book</h1>
            <p class="text-sm text-gray-600 mt-1">Manage your shipping and billing addresses</p>
        </div>
        <a href="{{ route('customer.addresses.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm  bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200">
            <i class="fas fa-plus mr-2"></i> Add New Address
        </a>
    </div>

    <!-- Shipping Addresses Section -->
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Shipping Addresses</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($shippingAddresses as $address)
            <div class="border rounded-lg p-4 relative {{ $address->is_default ? 'border-primary-500 bg-primary-50' : 'border-gray-200' }}">
                @if($address->is_default)
                <span class="absolute top-2 right-2 bg-primary-100 text-primary-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Default</span>
                @endif
                <h3 class="font-medium text-gray-900">{{ $address->full_name }}</h3>
                <p class="text-sm text-gray-600">{{ $address->street_address }}</p>
                <p class="text-sm text-gray-600">{{ $address->union_name }}, {{ $address->upazila_name }}, {{ $address->district_name }}-{{ $address->postal_code }}</p>
                <p class="text-sm text-gray-600">{{ $address->country }}</p>
                <p class="text-sm text-gray-600 mt-2"><i class="fas fa-phone mr-1"></i> {{ $address->phone }}</p>
                
                <div class="mt-4 flex flex-wrap gap-2">
                    <a  href="{{ route('customer.addresses.edit', ['address_id' => $address->id, 'user_id' => $address->user_id]) }}" class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200">
                        <i class="fas fa-pencil-alt mr-1 text-xs"></i> Edit
                    </a>
                    <form action="{{ route('customer.addresses.destroy', $address->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this address?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
                            <i class="fas fa-trash-alt mr-1 text-xs"></i> Delete
                        </button>
                    </form>
                    @if(!$address->is_default)
                    <form action="{{ route('customer.addresses.set-default', ['address_id' => $address->id, 'user_id' => $address->user_id])}}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-primary-700 bg-primary-100 hover:bg-primary-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200">
                            <i class="fas fa-check-circle mr-1 text-xs"></i> Set Default
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-8">
                <i class="fas fa-map-marker-alt text-gray-400 text-4xl mb-3"></i>
                <p class="text-gray-600">No shipping addresses saved yet</p>
            </div>
            @endforelse
        </div>
    </div>

    <!-- Billing Addresses Section -->
    <div class="px-6 py-4">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Billing Addresses</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse($billingAddresses as $address)
            <div class="border rounded-lg p-4 relative {{ $address->is_default ? 'border-primary-500 bg-primary-50' : 'border-gray-200' }}">
                @if($address->is_default)
                <span class="absolute top-2 right-2 bg-primary-100 text-primary-800 text-xs font-medium px-2.5 py-0.5 rounded-full">Default</span>
                @endif
                <h3 class="font-medium text-gray-900">{{ $address->full_name }}</h3>
                <p class="text-sm text-gray-600">{{ $address->street_address }}</p>
                <p class="text-sm text-gray-600">{{ $address->union_name }}, {{ $address->upazila_name }}, {{ $address->district_name }}-{{ $address->postal_code }}</p>
                <p class="text-sm text-gray-600">{{ $address->country }}</p>
                <p class="text-sm text-gray-600 mt-2"><i class="fas fa-phone mr-1"></i> {{ $address->phone }}</p>
                
                <div class="mt-4 flex flex-wrap gap-2">
                    <a href="{{ route('customer.addresses.edit', ['address_id' => $address->id, 'user_id' => $address->user_id]) }}" class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200">
                        <i class="fas fa-pencil-alt mr-1 text-xs"></i> Edit
                    </a>
                    <form action="{{ route('customer.addresses.destroy', $address->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this address?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors duration-200">
                            <i class="fas fa-trash-alt mr-1 text-xs"></i> Delete
                        </button>
                    </form>
                    @if(!$address->is_default)
                    <form action="{{ route('customer.addresses.set-default', ['address_id' => $address->id, 'user_id' => $address->user_id]) }}" method="POST">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-primary-700 bg-primary-100 hover:bg-primary-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-colors duration-200">
                            <i class="fas fa-check-circle mr-1 text-xs"></i> Set Default
                        </button>
                    </form>
                    @endif
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-8">
                <i class="fas fa-file-invoice-dollar text-gray-400 text-4xl mb-3"></i>
                <p class="text-gray-600">No billing addresses saved yet</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection