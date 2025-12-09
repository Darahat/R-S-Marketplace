@extends('frontend_view.layouts.home')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-800 to-gray-900 flex flex-col justify-center py-6 px-4 sm:py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <a href="{{ route('home') }}" class="block">
            <img class="mx-auto h-10 sm:h-12 w-auto" src="{{ asset('images/logo/logo.png') }}" alt="R&SMarketPlace">
        </a>
        <div class="mt-4 sm:mt-6 text-center">
            <h2 class="text-2xl sm:text-3xl font-extrabold text-white">
                Admin Portal
            </h2>
            <p class="mt-2 text-sm text-gray-400">
                Sign in to access admin dashboard
            </p>
        </div>
    </div>

    <div class="mt-6 sm:mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-6 px-4 shadow-2xl sm:rounded-xl sm:py-8 sm:px-10">

            @if ($errors->any())
                <div class="mb-4 bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-lg">
                    <ul class="list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.checklogin') }}" class="space-y-6">
                @csrf

                <!-- Email -->
                <div>
                    <label for="admin_email" class="block text-sm font-medium text-gray-700">
                        Admin Email
                    </label>
                    <div class="mt-1 relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-user-shield text-gray-400"></i>
                        </div>
                        <input id="admin_email" name="email" type="email" autocomplete="email" required
                               value="{{ old('email') }}"
                               class="appearance-none block w-full pl-12 pr-4 py-3 text-base border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-secondary focus:border-secondary transition-all">
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label for="admin_password" class="block text-sm font-medium text-gray-700">
                        Password
                    </label>
                    <div class="mt-1 relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input id="admin_password" name="password" type="password" autocomplete="current-password" required
                               class="appearance-none block w-full pl-12 pr-12 py-3 text-base border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-secondary focus:border-secondary transition-all">
                        <button type="button" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-500 hover:text-gray-700 transition-colors" onclick="togglePassword('admin_password')">
                            <i class="far fa-eye text-lg"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="admin_remember" name="remember" type="checkbox"
                               class="h-4 w-4 sm:h-5 sm:w-5 text-secondary focus:ring-secondary border-gray-300 rounded">
                        <label for="admin_remember" class="ml-2 block text-sm sm:text-base text-gray-900">
                            Remember me
                        </label>
                    </div>
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="submit"
                            class="w-full flex justify-center items-center py-3 sm:py-3.5 px-4 border border-transparent rounded-lg shadow-md text-base font-semibold text-white bg-gradient-to-r from-secondary to-primary hover:from-primary hover:to-secondary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-secondary transition-all duration-300 hover:shadow-lg active:scale-95">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        Sign In as Admin
                    </button>
                </div>
            </form>

            <!-- Divider -->
            <div class="mt-6">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white text-gray-500">
                            Not an admin?
                        </span>
                    </div>
                </div>

                <div class="mt-6 text-center">
                    <a href="{{ route('login') }}" class="text-sm text-primary hover:text-secondary font-medium transition-colors">
                        <i class="fas fa-arrow-left mr-1"></i>
                        Go to Customer Login
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function togglePassword(id) {
        const input = document.getElementById(id);
        const icon = event.currentTarget.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
</script>
@endpush
@endsection
