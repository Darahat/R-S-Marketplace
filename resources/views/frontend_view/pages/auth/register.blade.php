<div class="bg-gray-50 flex flex-col justify-center px-4 py-4 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center space-y-2">
        <a href="{{ route('home') }}" class="inline-block">
            <img class="mx-auto h-9 sm:h-10 w-auto" src="{{ asset('images/logo/logo.png') }}" alt="R&SMarketPlace">
        </a>
        <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Create a new account</h2>
        <p class="text-xs sm:text-sm text-gray-600">
            Already have an account?
            <button type="button" id="open-login-inline" class="font-semibold text-primary hover:text-secondary transition-colors">
                Sign in
            </button>
        </p>
    </div>

    <div class="mt-4 sm:mt-5 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-4 px-4 shadow-lg sm:rounded-lg sm:py-5 sm:px-6 max-h-[78vh] overflow-hidden">
            <div class="space-y-4" id="registration-form">
                @csrf

                <div class="flex items-center justify-between text-xs font-semibold text-gray-600">
                    <div class="flex items-center space-x-2">
                        <span id="step-1-indicator" class="h-2 w-2 rounded-full bg-primary"></span>
                        <span>Step 1: Profile</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span id="step-2-indicator" class="h-2 w-2 rounded-full bg-gray-300"></span>
                        <span>Step 2: Security</span>
                    </div>
                </div>

                <div id="register-step-1" class="space-y-3">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-xs font-medium text-gray-700">Full Name</label>
                        <div class="mt-1">
                            <input id="name" name="name" type="text" autocomplete="name" required
                                   value="{{ old('name') }}"
                                   class="appearance-none block w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                        </div>
                        @error('name')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-xs font-medium text-gray-700">Email address</label>
                        <div class="mt-1">
                            <input id="email" name="email" type="email" autocomplete="email" required
                                   value="{{ old('email') }}"
                                   class="appearance-none block w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                        </div>
                        @error('email')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Phone -->
                    <div>
                        <label for="mobile" class="block text-xs font-medium text-gray-700">Phone Number</label>
                        <div class="mt-1">
                            <input id="mobile" name="mobile" type="tel" autocomplete="tel" required
                                   value="{{ old('mobile') }}"
                                   class="appearance-none block w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                        </div>
                        @error('mobile')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div id="register-step-2" class="space-y-3 hidden">
                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-xs font-medium text-gray-700">Password</label>
                        <div class="mt-1 relative">
                            <input id="password" name="password" type="password" autocomplete="new-password" required
                                   class="appearance-none block w-full px-3 py-2.5 text-sm pr-10 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                            <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700 transition-colors" onclick="togglePassword('password')">
                                <i class="far fa-eye text-base"></i>
                            </button>
                        </div>
                        @error('password')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label for="password_confirmation" class="block text-xs font-medium text-gray-700">Confirm Password</label>
                        <div class="mt-1 relative">
                            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required
                                   class="appearance-none block w-full px-3 py-2.5 text-sm pr-10 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                            <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700 transition-colors" onclick="togglePassword('password_confirmation')">
                                <i class="far fa-eye text-base"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Terms -->
                    <div class="flex items-start">
                        <input id="terms" name="terms" type="checkbox" required
                               class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded mt-0.5">
                        <label for="terms" class="ml-2 block text-xs sm:text-sm text-gray-900">
                            I agree to the <a href="#" class="text-primary hover:text-secondary underline">Terms</a> and <a href="#" class="text-primary hover:text-secondary underline">Privacy Policy</a>
                        </label>
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <button type="button" id="register-prev" class="text-xs text-gray-600 font-semibold px-3 py-2 rounded-lg border border-gray-200 hover:bg-gray-50 transition hidden">Back</button>
                    <div class="flex-1"></div>
                    <button type="button" id="register-next" class="text-xs sm:text-sm font-semibold px-4 py-2 bg-primary text-white rounded-lg shadow hover:bg-primary-dark transition">Next</button>
                    <button type="button" id="submit-button" class="text-xs sm:text-sm font-semibold px-4 py-2 bg-primary text-white rounded-lg shadow hover:bg-primary-dark transition hidden">Register</button>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
     $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
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
        let currentStep = 1;
        const totalSteps = 2;

        function updateStepper() {
            $('#register-step-1').toggleClass('hidden', currentStep !== 1);
            $('#register-step-2').toggleClass('hidden', currentStep !== 2);
            $('#register-prev').toggleClass('hidden', currentStep === 1);
            $('#register-next').toggleClass('hidden', currentStep === totalSteps);
            $('#submit-button').toggleClass('hidden', currentStep !== totalSteps);
            $('#step-1-indicator').toggleClass('bg-primary', currentStep === 1).toggleClass('bg-gray-300', currentStep !== 1);
            $('#step-2-indicator').toggleClass('bg-primary', currentStep === 2).toggleClass('bg-gray-300', currentStep !== 2);
        }

        $('#register-next').on('click', function () {
            currentStep = Math.min(totalSteps, currentStep + 1);
            updateStepper();
        });

        $('#register-prev').on('click', function () {
            currentStep = Math.max(1, currentStep - 1);
            updateStepper();
        });

        // Show login form inline (modal switch)
        $('#open-login-inline').on('click', function (e) {
            e.preventDefault();
            if ($('#loginModal').length > 0) {
                $('#registerModal').addClass('hidden').removeClass('flex');
                $('#loginModal').removeClass('hidden').addClass('flex');
            }
        });

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

        updateStepper();
    });
</script>
@endpush
