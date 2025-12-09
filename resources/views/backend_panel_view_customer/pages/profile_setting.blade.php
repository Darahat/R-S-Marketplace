@extends('backend_panel_view_customer.layouts.customer')

@section('title', 'My Profile')

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
                    <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">My Profile</span>
                </div>
            </li>
        </ol>
    </nav>
@endsection

@section('panel-content')
<div class="max-w-4xl mx-auto">
    <div id="ajax-alert" class="hidden fixed top-4 right-4 z-50 max-w-sm"></div>

    <div class="bg-white rounded-xl shadow-md overflow-hidden">
        <!-- Profile Header -->
        <div class="bg-gradient-to-r from-primary to-secondary p-6 text-white">
            <div class="flex flex-col md:flex-row items-center">
                <div class="relative mb-4 md:mb-0 md:mr-6">
                    <img src="{{ Auth::user()->profile_photo ? asset(Auth::user()->profile_photo) : asset('images/default-avatar.png') }}" 
     class="w-24 h-24 rounded-full border-4 border-white border-opacity-50 shadow-md profile-photo-preview">

                    <label for="profile_photo" class="absolute bottom-0 right-0 bg-white rounded-full p-2 shadow-md cursor-pointer hover:bg-gray-100 transition">
                        <i class="fas fa-camera text-primary"></i>
                        <input type="file" id="profile_photo" name="profile_photo" class="hidden">
                    </label>
                </div>
                <div class="text-center md:text-left">
                    <h2 class="text-2xl font-bold profile-name-preview">{{ Auth::user()->name }}</h2>
                    <p class="text-white text-opacity-80 profile-email-preview">{{ Auth::user()->email }}</p>
                    <p class="text-white text-opacity-80 profile-mobile-preview">{{ Auth::user()->mobile ?? 'Not provided' }}</p>
                </div>
            </div>
        </div>

        <!-- Profile Details -->
        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Name Field -->
            <div class="space-y-2">
                <div class="flex items-center">
                    <label for="name" class="block text-sm font-medium text-gray-700">Full Name</label>
                    <div class="tooltip ml-2" data-tip="Your full name as registered">
                        <i class="fas fa-info-circle text-primary cursor-pointer"></i>
                    </div>
                </div>
                <div class="flex">
                    <input type="text" id="name" name="name" value="{{ Auth::user()->name }}"
                        class="flex-1 border border-gray-300 rounded-l-md p-2 focus:ring-primary focus:border-primary">
                    <button class="btn-update bg-primary hover:bg-secondary text-white px-4 rounded-r-md transition-colors duration-200 flex items-center justify-center" 
                            data-field="name"
                            data-confirm="Are you sure you want to update your name?">
                        <i class="fas fa-check"></i>
                    </button>
                </div>
            </div>


            <!-- Email Field -->
            <div class="space-y-2">
                <div class="flex items-center">
                    <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                    <span class="tooltip ml-2" data-tip="Your registered email address">
                        <i class="fas fa-info-circle text-primary cursor-pointer"></i>
                    </span>
                </div>
                 <div class="flex">
                    <input type="email" id="email" name="email" value="{{ Auth::user()->email }}"
                        class="flex-1 border border-gray-300 rounded-l-md p-2 focus:ring-primary focus:border-primary">
                    <button class="btn-update bg-primary hover:bg-secondary text-white px-4 rounded-r-md transition-colors duration-200" 
                            data-field="email">
                        <i class="fas fa-check"></i>
                    </button>
                </div>
            </div>

            <!-- Mobile Field -->
            <div class="space-y-2">
                <div class="flex items-center"></div>
                    <label for="mobile" class="block text-sm font-medium text-gray-700">Mobile Number <span class="tooltip ml-2" data-tip="Your registered mobile number">
                        <i class="fas fa-info-circle text-primary cursor-pointer"></i>
                    </span></label>
                    
                 <div class="flex">
                    <input type="text" id="mobile" name="mobile" value="{{ Auth::user()->mobile }}"
                        class="flex-1 border border-gray-300 rounded-l-md p-2 focus:ring-primary focus:border-primary">
                    <button class="btn-update bg-primary hover:bg-secondary text-white px-4 rounded-r-md transition-colors duration-200" 
                            data-field="mobile">
                        <i class="fas fa-check"></i>
                    </button>
                </div>
            </div>

            <!-- Last Login Info -->
            <div class="space-y-2">
                <div class="flex items-center"></div>
                    <label for="last_login" class="block text-sm font-medium text-gray-700">Last Login  <span class="tooltip ml-2" data-tip="Your last login time">
                        <i class="fas fa-info-circle text-primary cursor-pointer"></i>
                    </span></label>
                   
                 <div class="bg-gray-50 p-3 rounded-md">
                    <p class="text-sm text-gray-600">
                        <i class="fas fa-clock mr-2 text-primary"></i>
                        {{$profile['last_login'] ?? 'Never logged in'}}
                    </p>
                    <p class="text-xs text-gray-500 mt-1">
                        From {{ Auth::user()->last_ip ?? 'Unknown IP' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Additional Info Section -->
        <div class="border-t border-gray-200 px-6 py-4 bg-gray-50">
            <h3 class="text-lg font-medium text-gray-900 mb-3">Account Details</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <p class="text-sm text-gray-600"><i class="fas fa-user-shield mr-2 text-primary"></i> Member since {{ Auth::user()->created_at->format('M d, Y') }}</p>
                </div>
                <div>
                    <p class="text-sm text-gray-600"><i class="fas fa-id-card mr-2 text-primary"></i> User ID: {{ Auth::user()->id }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function () {
        // Initialize tooltips
 
        // Update fields
        $('.btn-update').on('click', function () {
            const field = $(this).data('field');
            const value = $(`#${field}`).val();
            const button = $(this);
            
            button.html('<i class="fas fa-spinner fa-spin"></i>');
            button.prop('disabled', true);

            $.ajax({
                url: '{{ route('customer.profile.update') }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    field: field,
                    value: value
                },
                success: function (response) {
                    // Show success notification
                    showToast('success', response.message);
                    
                    // Update previews
                    if (field === 'name') {
                        $('.profile-name-preview').text(value);
                    } else if (field === 'email') {
                        $('.profile-email-preview').text(value);
                    } else if (field === 'mobile') {
                        $('.profile-mobile-preview').text(value);
                    }
                },
                error: function (xhr) {
                    const errors = xhr.responseJSON.errors;
                    showToast('error', errors[Object.keys(errors)[0]][0]);
                },
                complete: function () {
                    button.html('<i class="fas fa-check"></i>');
                    button.prop('disabled', false);
                }
            });
        });

        // Profile photo upload
        $('#profile_photo').on('change', function() {
            if (this.files.length > 0) {
                const button = $('.btn-photo-update');
                button.html('<i class="fas fa-spinner fa-spin"></i> Uploading...');
                button.prop('disabled', true);

                const formData = new FormData();
                formData.append('field', 'profile_photo');
                formData.append('profile_photo', this.files[0]);
                formData.append('_token', '{{ csrf_token() }}');

                $.ajax({
                    url: '{{ route('customer.profile.update') }}',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        showToast('success', response.message);
                        // Refresh photo with cache busting
                        $.get('{{ route('customer.profile.photo') }}', function (data) {
                        $('.profile-photo-preview').attr('src', data.photo_url + '?t=' + new Date().getTime());
                    });                    },
                    error: function (xhr) {
                        const errors = xhr.responseJSON.errors;
                        showToast('error', errors[Object.keys(errors)[0]][0]);
                    },
                    complete: function () {
                        button.html('Update Photo');
                        button.prop('disabled', false);
                    }
                });
            }
        });

        // Toast notification function
     
    });
</script>
<style>
    .animate-fade-in {
        animation: fadeIn 0.3s ease-in-out;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush
@endsection