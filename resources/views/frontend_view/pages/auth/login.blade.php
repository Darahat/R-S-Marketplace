<div class="bg-gray-50 flex flex-col justify-center px-4 py-4 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center space-y-2">
        <a href="{{ route('home') }}" class="inline-block">
            <img class="mx-auto h-9 sm:h-10 w-auto" src="{{ asset('images/logo/logo.png') }}" alt="R&SMarketPlace">
        </a>
        <h2 class="text-xl sm:text-2xl font-bold text-gray-900">Sign in to your account</h2>
        <p class="text-xs sm:text-sm text-gray-600">
            Or
            <button type="button" id="open-register-inline" class="font-semibold text-primary hover:text-secondary transition-colors">
                create a new account
            </button>
        </p>
    </div>

    <div class="mt-4 sm:mt-5 sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-4 px-4 shadow-lg sm:rounded-lg sm:py-5 sm:px-6 max-h-[78vh] overflow-hidden">


            <div id="login-box" class="space-y-4">
                <!-- CSRF token stored as meta (already in head) -->
                <!-- Email -->
                <div>
                    <label for="email" class="block text-xs font-medium text-gray-700">
                        Email address
                    </label>
                    <div class="mt-1">
                        <input id="login_email" name="email" type="email" autocomplete="email" required
                               class="appearance-none block w-full px-3 py-2.5 text-sm border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-xs font-medium text-gray-700">
                        Password
                    </label>
                    <div class="mt-1 relative">
                        <input id="login_password" name="password" type="password" autocomplete="current-password" required
                               class="appearance-none block w-full px-3 py-2.5 text-sm pr-10 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary focus:border-primary transition-all">
                        <button type="button" class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-500 hover:text-gray-700 transition-colors" onclick="togglePassword('login_password')">
                            <i class="far fa-eye text-base"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember & Submit -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember_me" name="remember" type="checkbox"
                               class="h-4 w-4 text-primary focus:ring-primary border-gray-300 rounded">
                        <label for="remember" class="ml-2 block text-xs sm:text-sm text-gray-900">
                            Remember me
                        </label>
                    </div>
                </div>

                <!-- Submit Button -->
                <div>
                    <button id="loginBtn"
                            class="w-full flex justify-center py-2.5 sm:py-3 px-4 border border-transparent rounded-lg shadow-md text-sm font-semibold text-white bg-primary hover:bg-primary-dark focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all duration-300 hover:shadow-lg active:scale-95">
                        Sign in
                    </button>
                </div>
            </div>



            <!-- Social Auth -->
            <div class="mt-4">
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300"></div>
                    </div>
                    <div class="relative flex justify-center text-xs">
                        <span class="px-2 bg-white text-gray-500">
                            Or continue with
                        </span>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3">
                    <div>
                        <a href="#" class="w-full inline-flex justify-center items-center py-2.5 px-3 border border-gray-300 rounded-lg shadow-sm bg-white text-xs font-medium text-gray-500 hover:bg-gray-50 transition-all active:scale-95">
                            <i class="fab fa-facebook-f text-blue-600 text-base"></i>
                        </a>
                    </div>

                    <div>
                        <a href="#" class="w-full inline-flex justify-center items-center py-2.5 px-3 border border-gray-300 rounded-lg shadow-sm bg-white text-xs font-medium text-gray-500 hover:bg-gray-50 transition-all active:scale-95">
                            <i class="fab fa-google text-red-600 text-base"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>

    // Set CSRF token globally for all AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Show register form inline (modal switch)
    $('#open-register-inline').on('click', function (e) {
        e.preventDefault();
        const hasModal = $('#registerModal').length > 0;
        if (hasModal) {
            $('#loginModal').addClass('hidden').removeClass('flex');
            $('#registerModal').removeClass('hidden').addClass('flex');
        }
    });

    // On login button click
    $('#loginBtn').on('click', function () {
        const email = $('#login_email').val();
        const password = $('#login_password').val();
        const remember = $('#remember_me').is(':checked') ? 'on' : '';

        $.ajax({
            url: "{{ route('checklogin') }}",
            type: "POST",
            data: {
                email: email,
                password: password,
                remember: remember
            },
            success: function (response) {
                // Handle successful login
                console.log('Login success:', response);
                window.location.href = "{{ route('home') }}"; // Redirect after login
            },
            error: function (xhr) {
                // Handle error (e.g., validation)
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    let msg = '';
                    for (let key in errors) {
                        msg += errors[key][0] + '\n';
                    }
                    alert(msg);
                } else {
                    alert('Login failed. Please try again.');
                }
            }
        });
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
</script>
@endpush
