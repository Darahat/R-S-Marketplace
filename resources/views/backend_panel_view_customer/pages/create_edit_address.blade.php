@extends('backend_panel_view_customer.layouts.customer')

@section('title', isset($address) ? 'Edit Address' : 'Add New Address')

@section('breadcrumbs')
    <nav class="flex" aria-label="Breadcrumb">
        <ol class="inline-flex items-center space-x-1 md:space-x-3">
            <li class="inline-flex items-center">
                <a href="{{ route('customer.dashboard') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-primary-600 transition-colors duration-200">
                    <i class="fas fa-home mr-2"></i>
                    Home
                </a>
            </li>
            <li class="inline-flex items-center">
                <a href="{{ route('customer.addresses.index') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-primary-600 transition-colors duration-200">
                    <i class="fas fa-address-book mr-2"></i>
                    Address Book
                </a>
            </li>
            <li aria-current="page">
                <div class="flex items-center">
                    <i class="fas fa-chevron-right text-gray-400 mx-2 text-xs"></i>
                    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">{{ isset($address) ? 'Edit Address' : 'Add New Address' }}</span>
                </div>
            </li>
        </ol>
    </nav>
@endsection

@section('panel-content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm overflow-hidden">
        <!-- Form Header -->
        <div class="px-6 py-5 border-b border-gray-100 bg-gray-50">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-gray-800">{{ isset($address) ? 'Edit Address' : 'Add New Address' }}</h1>
                    <p class="mt-1 text-sm text-gray-500">Please fill in all required fields</p>
                </div>
                <div class="flex-shrink-0">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-primary-100 text-primary-800">
                        <i class="fas fa-map-marker-alt mr-1"></i>
                        {{ isset($address) ? ucfirst($address->address_type) : 'New' }} Address
                    </span>
                </div>
            </div>
        </div>

        <!-- Form Content -->
        <form action="{{ isset($address) ? route('customer.addresses.update', ['address' => $address->id, 'user_id' => $address->user_id]) : route('customer.addresses.store') }}" method="POST" class="divide-y divide-gray-200">
            @csrf
            @if(isset($address))
                @method('PUT')
            @endif

            <div class="px-6 py-5 space-y-6">
                <!-- Address Type -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-6">
                    <div class="sm:col-span-6">
                        <label for="address_type" class="block text-sm font-medium text-gray-700 mb-1">Address Type <span class="text-red-500">*</span></label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <select id="address_type" name="address_type" class="form-select appearance-none block w-full pl-3 pr-10 py-3 text-base border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-lg transition-all duration-150">
                                <option value="" >-- Select Type --</option>
                                <option value="shipping" {{ old('address_type', isset($address) ? $address->address_type : '') == 'shipping' ? 'selected' : '' }}>Shipping Address</option>
                                <option value="billing" {{ old('address_type', isset($address) ? $address->address_type : '') == 'billing' ? 'selected' : '' }}>Billing Address</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <i class="fas fa-chevron-down text-gray-400"></i>
                            </div>
                        </div>
                        @error('address_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Name and Phone -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-6">
                    <div class="sm:col-span-3">
                        <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">Full Name <span class="text-red-500">*</span></label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <input type="text" name="full_name" id="full_name" value="{{ old('full_name', isset($address) ? $address->full_name : '') }}"
                                   class="form-input block w-full px-4 py-3 border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-lg transition-all duration-150"
                                   placeholder="John Doe" required>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                <i class="fas fa-user text-gray-400"></i>
                            </div>
                        </div>
                        @error('full_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-3">
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number <span class="text-red-500">*</span></label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <input type="text" name="phone" id="phone" value="{{ old('phone', isset($address) ? $address->phone : '') }}"
                                   class="form-input block w-full px-4 py-3 border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-lg transition-all duration-150"
                                   placeholder="01700123456" required>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                <i class="fas fa-phone text-gray-400"></i>
                            </div>
                        </div>
                        @error('phone')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Street Address -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-6">
                    <div class="sm:col-span-6">
                        <label for="street_address" class="block text-sm font-medium text-gray-700 mb-1">Street Address <span class="text-red-500">*</span></label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <input type="text" name="street_address" id="street_address" value="{{ old('street_address', isset($address) ? $address->street_address : '') }}"
                                   class="form-input block w-full px-4 py-3 border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-lg transition-all duration-150"
                                   placeholder="123 Main Road">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                <i class="fas fa-map-marked-alt text-gray-400"></i>
                            </div>
                        </div>
                        @error('street_address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- City, State, Postal Code -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-6">
                    <!-- district, upazilla, union -->
                     <div class="sm:col-span-2">
                        <label for="district_id" class="block text-sm font-medium text-gray-700 mb-1">District <span class="text-red-500">*</span></label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                                <select id="district_id" name="district_id" class="form-select appearance-none block w-full pl-3 pr-10 py-3 text-base border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-lg transition-all duration-150">
                                <option value="">--Select District--</option>
                                @foreach($district as $districtItem)
                                    <option value="{{ $districtItem->id }}" @if(old('district_id', isset($address) ? $address->district_id : '') == $districtItem->id) selected @endif>{{ $districtItem->name }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <i class="fas fa-chevron-down text-gray-400"></i>
                            </div>
                        </div>
                          @error('district_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="upazila_id" class="block text-sm font-medium text-gray-700 mb-1">Upazila <span class="text-red-500">*</span></label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <select id="upazila_id" name="upazila_id" class="form-select appearance-none block w-full pl-3 pr-10 py-3 text-base border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-lg transition-all duration-150">
                            <option value="">--Select Upazila--</option>
                        </select>
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <i class="fas fa-chevron-down text-gray-400"></i>
                            </div>

                        </div>
                         @error('upazila_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <label for="union_id" class="block text-sm font-medium text-gray-700 mb-1">Union <span class="text-red-500">*</span></label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <select id="union_id" name="union_id" class="form-select appearance-none block w-full pl-3 pr-10 py-3 text-base border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-lg transition-all duration-150">
                            <option value="">--Select Union--</option>
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <i class="fas fa-chevron-down text-gray-400"></i>
                            </div>
                        </div>
                         @error('union_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- end district, upazilla, union -->



                    <div class="sm:col-span-2">
                        <label for="postal_code" class="block text-sm font-medium text-gray-700 mb-1">Postal Code <span class="text-red-500">*</span></label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <input type="text" name="postal_code" id="postal_code" value="{{ old('postal_code', isset($address) ? $address->postal_code : '') }}"
                                   class="form-input block w-full px-4 py-3 border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-lg transition-all duration-150"
                                   placeholder="10001">
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                <i class="fas fa-mail-bulk text-gray-400"></i>
                            </div>
                        </div>
                        @error('postal_code')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>


                </div>

                <!-- Country and Default -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-6">
                    <div class="sm:col-span-3">
                        <label for="country" class="block text-sm font-medium text-gray-700 mb-1">Country <span class="text-red-500">*</span></label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <select id="country" name="country" class="form-select appearance-none block w-full pl-3 pr-10 py-3 text-base border-gray-300 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 sm:text-sm rounded-lg transition-all duration-150">
                                @foreach(['Bangladesh'] as $country)
                                    <option value="{{ $country }}" {{ old('country', isset($address) ? $address->country : '') == $country ? 'selected' : '' }}>{{ $country }}</option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                <i class="fas fa-globe text-gray-400"></i>
                            </div>
                        </div>
                        @error('country')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-3 flex items-end">
                        <div class="flex items-center h-10 mt-1">
                            <!-- Hidden input to ensure a value is always sent -->
                            <input type="hidden" name="is_default" value="0">

                            <input id="is_default" name="is_default" type="checkbox"
                                   class="focus:ring-primary-500 h-5 w-5 text-primary-600 border-gray-300 rounded transition-all duration-150"
                                   value="1"
                                   {{ old('is_default', isset($address) ? $address->is_default : false) ? 'checked' : '' }}>
                            <label for="is_default" class="ml-3 block text-sm text-gray-700">
                                Set as default {{ isset($address) ? $address->address_type : '' }} address
                            </label>
                        </div>
                    </div>

                </div>
            </div>

            <div class="px-6 py-4 bg-gray-50 flex justify-end space-x-3">
                <a href="{{ route('customer.addresses.index') }}" class="inline-flex items-center px-4 py-2.5 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-150">
                    <i class="fas fa-times mr-2"></i>
                    Cancel
                </a>

                <button type="submit" class="inline-flex items-center px-4 py-2.5 border border-transparent text-sm font-medium rounded-lg shadow-sm text-black bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 transition-all duration-150">
                    <i class="fas fa-save mr-2"></i>
                    {{ isset($address) ? 'Update Address' : 'Save Address' }}
                </button>
            </div>
        </form>
    </div>
</div>


@push('styles')
<style>
    .form-input, .form-select {
        transition: all 0.2s ease;
        border: 1px solid #d1d5db;
    }
    .form-input:focus, .form-select:focus {
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    .form-input:hover, .form-select:hover {
        border-color: #9ca3af;
    }
</style>
@endpush
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const rawDistricts = @json($district);
        const toArray = function (value) {
            if (Array.isArray(value)) {
                return value;
            }
            if (value && typeof value === 'object') {
                return Object.values(value);
            }
            return [];
        };
        const districts = toArray(rawDistricts);
        const selectedDistrictId = "{{ old('district_id', isset($address) ? $address->district_id : '') }}";
        const selectedUpazilaId = "{{ old('upazila_id', isset($address) ? $address->upazila_id : '') }}";
        const selectedUnionId = "{{ old('union_id', isset($address) ? $address->union_id : '') }}";

        const districtSelect = document.getElementById('district_id');
        const upazilaSelect = document.getElementById('upazila_id');
        const unionSelect = document.getElementById('union_id');

        if (!districtSelect || !upazilaSelect || !unionSelect) {
            return;
        }

        function setDefaultOption(selectEl, placeholder) {
            selectEl.innerHTML = '';
            const option = document.createElement('option');
            option.value = '';
            option.textContent = placeholder;
            selectEl.appendChild(option);
        }

        function appendOption(selectEl, value, label, isSelected) {
            const option = document.createElement('option');
            option.value = String(value);
            option.textContent = label;
            option.selected = Boolean(isSelected);
            selectEl.appendChild(option);
        }

        function populateUnions(districtId, upazilaId, selectedUnion) {
            const district = districts.find(function (item) {
                return String(item.id) === String(districtId);
            });
            const upazilas = district ? toArray(district.upazila) : [];
            const upazila = upazilas.length
                ? upazilas.find(function (item) {
                    return String(item.id) === String(upazilaId);
                })
                : null;
            const unions = upazila ? toArray(upazila.union) : [];

            setDefaultOption(unionSelect, '--Select Union--');
            unions.forEach(function (item) {
                appendOption(unionSelect, item.id, item.name, String(item.id) === String(selectedUnion));
            });
        }

        function populateUpazilas(districtId, selectedUpazila) {
            const district = districts.find(function (item) {
                return String(item.id) === String(districtId);
            });
            const upazilas = district ? toArray(district.upazila) : [];

            setDefaultOption(upazilaSelect, '--Select Upazila--');
            setDefaultOption(unionSelect, '--Select Union--');

            upazilas.forEach(function (item) {
                appendOption(upazilaSelect, item.id, item.name, String(item.id) === String(selectedUpazila));
            });

            if (selectedUpazila) {
                populateUnions(districtId, selectedUpazila, selectedUnionId);
            }
        }

        districtSelect.addEventListener('change', function () {
            populateUpazilas(this.value);
        });

        upazilaSelect.addEventListener('change', function () {
            populateUnions(districtSelect.value, this.value);
        });

        if (selectedDistrictId) {
            populateUpazilas(selectedDistrictId, selectedUpazilaId);
        }
    });
</script>

@endsection
