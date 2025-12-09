<div class="min-h-screen bg-gray-50 flex flex-col justify-center py-6 px-4 sm:py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <a href="{{ route('home') }}" class="block">
            <img class="mx-auto h-10 sm:h-12 w-auto" src="{{ asset('images/logo/logo.png') }}" alt="R&SMarketPlace">
        </a>
        <h2 class="mt-4 sm:mt-6 text-center text-2xl sm:text-3xl font-extrabold text-gray-900">
            Create a new account
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
            Already have an account?
            <a href="{{ route('login') }}" class="font-medium text-primary hover:text-secondary transition-colors">
                Sign in
            </a>
        </p>
    </div>

    <div class="mt-6 sm:mt-8 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-6 px-4 shadow-lg sm:rounded-lg sm:py-8 sm:px-10">
            <div class="space-y-6" id="registration-form">
                @csrf

                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">
                        Full Name
                    </label>
                    <div class="mt-1">
                        <input id="name" name="name" type="text" autocomplete="name" required
                               value="{{ old('name') }}"
                               class="appearance-none block w-full px-4 py-3 text-base border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                    </div>
                    @error('name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">
                        Email address
                    </label>
                    <div class="mt-1">
                        <input id="email" name="email" type="email" autocomplete="email" required
                               value="{{ old('email') }}"
                               class="appearance-none block w-full px-4 py-3 text-base border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                    </div>
                    @error('email')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Phone -->
                <div>
                    <label for="mobile" class="block text-sm font-medium text-gray-700">
                        Phone Number
                    </label>
                    <div class="mt-1">
                        <input id="mobile" name="mobile" type="tel" autocomplete="tel" required
                               value="{{ old('mobile') }}"
                               class="appearance-none block w-full px-4 py-3 text-base border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                    </div>
                    @error('mobile')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">
                        Password
                    </label>
                    <div class="mt-1 relative">
                        <input id="password" name="password" type="password" autocomplete="new-password" required
                               class="appearance-none block w-full px-4 py-3 text-base pr-12 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                        <button type="button" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-500 hover:text-gray-700 transition-colors" onclick="togglePassword('password')">
                            <i class="far fa-eye text-lg"></i>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                        Confirm Password
                    </label>
                    <div class="mt-1 relative">
                        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required
                               class="appearance-none block w-full px-4 py-3 text-base pr-12 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                        <button type="button" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-500 hover:text-gray-700 transition-colors" onclick="togglePassword('password_confirmation')">
                            <i class="far fa-eye text-lg"></i>
                        </button>
                    </div>
                </div>

                <!-- Terms -->
                <div class="flex items-start">
                    <input id="terms" name="terms" type="checkbox" required
                           class="h-4 w-4 sm:h-5 sm:w-5 mt-0.5 text-primary focus:ring-primary border-gray-300 rounded">
                    <label for="terms" class="ml-2 block text-sm sm:text-base text-gray-900">
                        I agree to the <a href="#" class="text-primary hover:text-secondary underline">Terms</a> and <a href="#" class="text-primary hover:text-secondary underline">Privacy Policy</a>
                    </label>
                </div>

                <!-- Submit Button -->
                <div>
                    <button type="button" id="submit-button"
                            class="w-full flex justify-center py-3 sm:py-3.5 px-4 border border-transparent rounded-lg shadow-md text-base font-semibold text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all duration-300 hover:shadow-lg active:scale-95">
                        Register
                    </button>
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

    $(document).ready(function () {
        $('#submit-button').on('click', function () {
            var formData = {
                _token: $('input[name="_token"]').val(),
                name: $('#name').val(),
                email: $('#email').val(),
                mobile: $('#mobile').val(),
                password: $('#password').val(),
                password_confirmation: $('#password_confirmation').val(),
                terms: $('#terms').prop('checked') ? 1 : 0,
            };

            $.ajax({
                url: '{{ route("register") }}',
                type: 'POST',
                data: formData,
                success: function (response) {
                    // Handle success (e.g., redirect to a different page or show a success message)
                },
                error: function (error) {
                    // Handle errors (e.g., show validation errors)
                }
            });
        });
    });
</script>
@endpush
